<?php
/**
 * Public DueDate Controller
 * HoanKiem LAB - Allows updating delivery date without login via tokenized link
 */

class DueDateController extends Controller
{
    public function show(string $token): void
    {
        $order = $this->db->fetch("SELECT * FROM orders WHERE due_date_token = ?", [$token]);
        if (!$order) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $this->view('public.due_date_change', [
            'pageTitle' => 'Chỉnh sửa ngày trả hàng',
            'order' => $order,
            'token' => $token
        ]);
    }

    public function update(string $token): void
    {
        if (!verify_csrf()) { $this->redirect("/order/change-due-date/{$token}", ['error' => 'CSRF error']); return; }

        $order = $this->db->fetch("SELECT * FROM orders WHERE due_date_token = ?", [$token]);
        if (!$order) {
            $this->redirect('/', ['error' => 'Đơn hàng không hợp lệ']);
            return;
        }

        $adjustedDate = $this->input('adjusted_due_date');
        $reason = $this->input('reason', '');

        if (!$adjustedDate) {
            $this->redirect("/order/change-due-date/{$token}", ['error' => 'Vui lòng chọn ngày trả hàng mong muốn']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $this->db->update('orders', [
                'adjusted_due_date' => $adjustedDate
            ], 'id = ?', [$order->id]);

            // Save order status log
            $this->db->insert('order_status_logs', [
                'order_id' => $order->id,
                'from_status' => format_date($order->due_date),
                'to_status' => format_date($adjustedDate),
                'status_type' => 'overall',
                'changed_by' => null, // Guest public action
                'notes' => "Yêu cầu thay đổi ngày trả hàng thành: " . format_date($adjustedDate) . ". Lý do: " . $reason,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify admin
            $admins = $this->db->fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
            foreach ($admins as $admin) {
                $this->notify($admin->id, 'order_status',
                    "Yêu cầu chỉnh ngày giao đơn {$order->order_code}",
                    "Khách yêu cầu đổi từ " . format_date($order->due_date) . " sang " . format_date($adjustedDate),
                    ['order_id' => $order->id]
                );
            }

            $this->db->commit();
            $this->redirect("/order/change-due-date/{$token}", ['success' => 'Đã gửi yêu cầu đổi ngày trả hàng thành công!']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect("/order/change-due-date/{$token}", ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
