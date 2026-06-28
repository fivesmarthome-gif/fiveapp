<?php
/**
 * Customer Order Controller
 * HoanKiem LAB
 */

class OrderController extends Controller
{
    public function index(): void
    {
        $auth     = Auth::getInstance();
        $customer = $auth->user();
        $page     = max(1, (int)$this->input('page', 1));
        $status   = $this->input('status', '');

        $where  = "o.customer_id = ?";
        $params = [$customer->id];

        if ($status) {
            if ($status === 'completed') {
                $where .= " AND o.overall_status = ?";
                $params[] = 'completed';
            } else {
                $where  .= " AND o.delivery_status = ?";
                $params[] = $status;
            }
        }

        $sql    = "SELECT o.* FROM orders o WHERE {$where} ORDER BY o.created_at DESC";
        $result = $this->db->paginate($sql, $params, $page, 10);

        $this->view('customer.orders.index', [
            'pageTitle'    => 'Đơn hàng của tôi',
            'orders'       => $result['data'],
            'pagination'   => $result,
            'filterStatus' => $status,
        ]);
    }

    public function show(string $id): void
    {
        $auth     = Auth::getInstance();
        $customer = $auth->user();

        $order = $this->db->fetch(
            "SELECT o.*, b.name AS branch_name FROM orders o LEFT JOIN branches b ON b.id=o.branch_id WHERE o.id=? AND o.customer_id=?",
            [(int)$id, $customer->id]
        );

        if (!$order) {
            $this->redirect('/customer/orders', ['error' => 'Không tìm thấy đơn hàng']);
            return;
        }

        $items = $this->db->fetchAll(
            "SELECT oi.*, pt.name AS product_name FROM order_items oi LEFT JOIN product_types pt ON pt.id=oi.product_type_id WHERE oi.order_id=?",
            [(int)$id]
        );

        // Get production progress (aggregate across items)
        $totalSteps    = $this->db->count('production_steps', 'order_id=?', [(int)$id]);
        $completedSteps= $this->db->count('production_steps', "order_id=? AND status='completed'", [(int)$id]);
        $currentStep   = $this->db->fetch(
            "SELECT * FROM production_steps WHERE order_id=? AND status='in_progress' ORDER BY step_number LIMIT 1",
            [(int)$id]
        );

        $statusLogs = $this->db->fetchAll(
            "SELECT * FROM order_status_logs WHERE order_id=? ORDER BY created_at ASC",
            [(int)$id]
        );

        $feedbacks = $this->db->fetchAll(
            "SELECT * FROM order_feedbacks WHERE order_id=? ORDER BY created_at DESC",
            [(int)$id]
        );

        $delivery = $this->db->fetch("SELECT * FROM deliveries WHERE order_id=? ORDER BY id DESC LIMIT 1", [(int)$id]);

        $payments = $this->db->fetchAll("SELECT * FROM payments WHERE order_id=? ORDER BY created_at DESC", [(int)$id]);

        $this->view('customer.orders.show', [
            'pageTitle'     => "Đơn #{$order->order_code}",
            'backUrl'       => url('/customer/orders'),
            'order'         => $order,
            'items'         => $items,
            'totalSteps'    => $totalSteps,
            'completedSteps'=> $completedSteps,
            'currentStep'   => $currentStep,
            'statusLogs'    => $statusLogs,
            'feedbacks'     => $feedbacks,
            'delivery'      => $delivery,
            'payments'      => $payments,
        ]);
    }

    public function feedback(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/customer/orders/{$id}", ['error' => 'CSRF error']); return; }

        $auth     = Auth::getInstance();
        $customer = $auth->user();

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=? AND customer_id=?", [(int)$id, $customer->id]);
        if (!$order) { $this->redirect('/customer/orders'); return; }

        $content = trim($this->input('content', ''));
        if (empty($content)) {
            $this->redirect("/customer/orders/{$id}", ['error' => 'Vui lòng nhập nội dung phản hồi']);
            return;
        }

        // Upload images
        $imagePaths = $this->uploadMultipleFiles('images', 'feedbacks');

        $this->db->insert('order_feedbacks', [
            'order_id'   => (int)$id,
            'customer_id'=> $customer->id,
            'content'    => $content,
            'rating'     => max(1, min(5, (int)$this->input('rating', 5))),
            'images'     => json_encode($imagePaths),
            'status'     => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify admin
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
        foreach ($admins as $admin) {
            $this->notify($admin->id, 'order_feedback',
                "Phản hồi mới từ {$customer->name}",
                "Đơn hàng {$order->order_code}: {$content}",
                ['order_id' => $id]
            );
        }

        $this->redirect("/customer/orders/{$id}", ['success' => 'Đã gửi phản hồi']);
    }

    public function confirm(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/customer/orders/{$id}", ['error' => 'CSRF error']); return; }

        $auth     = Auth::getInstance();
        $customer = $auth->user();

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=? AND customer_id=? AND delivery_status='delivered'", [(int)$id, $customer->id]);
        if (!$order) { $this->redirect("/customer/orders/{$id}", ['error' => 'Không thể xác nhận']); return; }

        $this->db->update('orders', [
            'delivery_status' => 'delivered',
            'overall_status'  => 'completed',
        ], 'id=?', [(int)$id]);

        $this->db->insert('order_status_logs', [
            'order_id'   => (int)$id,
            'from_status'=> $order->overall_status,
            'to_status'  => 'completed',
            'status_type'=> 'overall',
            'changed_by' => $customer->id,
            'notes'      => 'Khách hàng xác nhận nhận hàng',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->redirect("/customer/orders/{$id}", ['success' => 'Đã xác nhận nhận hàng thành công!']);
    }

    public function requestReturn(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/customer/orders/{$id}"); return; }

        $auth     = Auth::getInstance();
        $customer = $auth->user();

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=? AND customer_id=? AND delivery_status='delivered'", [(int)$id, $customer->id]);
        if (!$order) { $this->redirect("/customer/orders/{$id}", ['error' => 'Không thể yêu cầu hoàn trả']); return; }

        $reason = trim($this->input('reason', ''));

        $this->db->update('orders', ['delivery_status' => 'pending_return'], 'id=?', [(int)$id]);
        $this->db->update('deliveries', [
            'status'     => 'pending_return',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'order_id=?', [(int)$id]);

        $this->db->insert('order_status_logs', [
            'order_id'   => (int)$id,
            'from_status'=> 'delivered',
            'to_status'  => 'pending_return',
            'status_type'=> 'delivery',
            'changed_by' => $customer->id,
            'notes'      => "Khách yêu cầu hoàn trả: {$reason}",
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify admins
        $admins = $this->db->fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
        foreach ($admins as $admin) {
            $this->notify($admin->id, 'order_return',
                "Yêu cầu hoàn trả: {$order->order_code}",
                $reason ?: 'Khách hàng yêu cầu hoàn trả',
                ['order_id' => $id]
            );
        }

        $this->redirect("/customer/orders/{$id}", ['success' => 'Đã gửi yêu cầu hoàn trả. Admin sẽ xem xét.']);
    }
}
