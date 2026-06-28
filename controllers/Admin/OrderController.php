<?php
/**
 * Admin Order Controller
 * HoanKiem LAB
 */

class OrderController extends Controller
{
    public function index(): void
    {
        $page    = max(1, (int)$this->input('page', 1));
        $perPage = 20;
        $search  = trim($this->input('search', ''));
        $status  = $this->input('status', '');
        $priority= $this->input('priority', '');
        $dateFrom= $this->input('date_from', '');
        $dateTo  = $this->input('date_to', '');

        $where  = "1=1";
        $params = [];

        if ($search) {
            $where  .= " AND (o.order_code LIKE ? OR u.name LIKE ? OR u.clinic_name LIKE ?)";
            $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
        }
        if ($status) {
            $where  .= " AND o.overall_status = ?";
            $params[] = $status;
        }
        if ($priority) {
            $where  .= " AND o.priority = ?";
            $params[] = $priority;
        }
        if ($dateFrom) {
            $where  .= " AND o.received_date >= ?";
            $params[] = $dateFrom;
        }
        if ($dateTo) {
            $where  .= " AND o.received_date <= ?";
            $params[] = $dateTo;
        }

        $sql = "SELECT o.*, u.name AS customer_name, u.clinic_name, u.phone AS customer_phone
                FROM orders o
                LEFT JOIN users u ON u.id = o.customer_id
                WHERE {$where}
                ORDER BY o.created_at DESC";

        $result = $this->db->paginate($sql, $params, $page, $perPage);

        $customers  = $this->db->fetchAll("SELECT id, name, clinic_name FROM users WHERE role='customer' AND is_active=1 ORDER BY name");
        $productTypes = $this->db->fetchAll("SELECT * FROM product_types WHERE is_active=1 ORDER BY name");

        $this->view('admin.orders.index', [
            'pageTitle'    => 'Quản lý đơn hàng',
            'breadcrumbs'  => ['Dashboard' => url('/admin/dashboard'), 'Đơn hàng' => ''],
            'orders'       => $result['data'],
            'pagination'   => $result,
            'search'       => $search,
            'filterStatus' => $status,
            'filterPriority'=> $priority,
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
        ]);
    }

    public function create(): void
    {
        $customers   = $this->db->fetchAll("SELECT id, name, clinic_name, phone FROM users WHERE role='customer' AND is_active=1 ORDER BY name");
        $productTypes= $this->db->fetchAll("SELECT * FROM product_types WHERE is_active=1 ORDER BY name");
        $branches    = $this->db->fetchAll("SELECT * FROM branches WHERE is_active=1 ORDER BY name");
        $shades      = $this->appConfig['shades'];

        $this->view('admin.orders.create', [
            'pageTitle'   => 'Tạo đơn hàng',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Đơn hàng' => url('/admin/orders'), 'Tạo mới' => ''],
            'customers'   => $customers,
            'productTypes'=> $productTypes,
            'branches'    => $branches,
            'shades'      => $shades,
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) {
            $this->redirect('/admin/orders/create', ['error' => 'Yêu cầu không hợp lệ']);
            return;
        }

        $validation = $this->validate([
            'customer_id'   => 'required',
            'received_date' => 'required|date',
            'due_date'      => 'required|date',
            'priority'      => 'required|in:normal,urgent,emergency',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/orders/create');
            return;
        }

        $auth = Auth::getInstance();
        $orderCode = generate_order_code();

        $this->db->beginTransaction();
        try {
            $orderId = $this->db->insert('orders', [
                'order_code'        => $orderCode,
                'customer_id'       => (int)$this->input('customer_id'),
                'created_by'        => $auth->id(),
                'branch_id'         => $this->input('branch_id') ?: null,
                'received_date'     => $this->input('received_date'),
                'due_date'          => $this->input('due_date'),
                'priority'          => $this->input('priority', 'normal'),
                'notes'             => $this->input('notes', ''),
                'overall_status'    => 'new',
                'production_status' => 'pending',
                'delivery_status'   => 'none',
                'payment_status'    => 'unpaid',
                'total_amount'      => 0,
                'discount'          => 0,
                'paid_amount'       => 0,
                'product_images'    => '[]',
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            // Process order items
            $productIds   = $_POST['product_type_id'] ?? [];
            $quantities   = $_POST['quantity'] ?? [];
            $unitPrices   = $_POST['unit_price'] ?? [];
            $toothNumbers = $_POST['tooth_numbers'] ?? [];
            $shades       = $_POST['shade'] ?? [];
            $materials    = $_POST['material_type'] ?? [];
            $specs        = $_POST['specifications'] ?? [];

            $totalAmount = 0;
            foreach ($productIds as $i => $productId) {
                if (empty($productId)) continue;
                $qty   = max(1, (int)($quantities[$i] ?? 1));
                $pt = $this->db->fetch("SELECT name, base_price FROM product_types WHERE id=?", [(int)$productId]);
                $price = (float)($unitPrices[$i] ?? 0);
                if ($price <= 0) {
                    $price = $pt ? (float)$pt->base_price : 0;
                }
                $amount = $qty * $price;
                $totalAmount += $amount;

                $this->db->insert('order_items', [
                    'order_id'       => $orderId,
                    'product_type_id'=> (int)$productId,
                    'product_name'   => $pt ? $pt->name : 'Sản phẩm khác',
                    'tooth_numbers'  => $toothNumbers[$i] ?? '',
                    'shade'          => $shades[$i] ?? '',
                    'material_type'  => $materials[$i] ?? '',
                    'specifications' => $specs[$i] ?? '',
                    'quantity'       => $qty,
                    'unit_price'     => $price,
                    'amount'         => $amount,
                ]);
            }

            // Update total
            $discount = (float)$this->input('discount', 0);
            $this->db->update('orders', [
                'total_amount' => $totalAmount,
                'discount'     => $discount,
            ], 'id = ?', [$orderId]);

            // Status log
            $this->db->insert('order_status_logs', [
                'order_id'   => $orderId,
                'from_status'=> '',
                'to_status'  => 'new',
                'changed_by' => $auth->id(),
                'notes'      => 'Đơn hàng được tạo mới',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Notify customer
            $this->notify((int)$this->input('customer_id'), 'order_status',
                "Đơn hàng {$orderCode} đã được tạo",
                "Đơn hàng của bạn đang chờ xác nhận",
                ['order_id' => $orderId, 'order_code' => $orderCode]
            );

            $this->db->commit();
            $this->logActivity('create_order', 'orders', $orderId, "Tạo đơn hàng {$orderCode}");
            $this->redirect("/admin/orders/{$orderId}", ['success' => "Đơn hàng {$orderCode} đã được tạo"]);

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->redirect('/admin/orders/create', ['error' => 'Có lỗi xảy ra: ' . $e->getMessage()]);
        }
    }

    public function show(string $id): void
    {
        $order = $this->db->fetch(
            "SELECT o.*, u.name AS customer_name, u.clinic_name, u.phone AS customer_phone,
                    u.email AS customer_email, u.address AS customer_address,
                    b.name AS branch_name, c.name AS creator_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN branches b ON b.id = o.branch_id
             LEFT JOIN users c ON c.id = o.created_by
             WHERE o.id = ?",
            [(int)$id]
        );

        if (!$order) {
            http_response_code(404);
            $this->view('errors.404');
            return;
        }

        $items = $this->db->fetchAll(
            "SELECT oi.*, pt.name AS product_name
             FROM order_items oi
             LEFT JOIN product_types pt ON pt.id = oi.product_type_id
             WHERE oi.order_id = ?",
            [(int)$id]
        );

        $steps = $this->db->fetchAll(
            "SELECT ps.*, u.name AS staff_name
             FROM production_steps ps
             LEFT JOIN users u ON u.id = ps.assigned_to
             WHERE ps.order_id = ?
             ORDER BY ps.step_number",
            [(int)$id]
        );

        $statusLogs = $this->db->fetchAll(
            "SELECT sl.*, u.name AS changed_by_name
             FROM order_status_logs sl
             LEFT JOIN users u ON u.id = sl.changed_by
             WHERE sl.order_id = ?
             ORDER BY sl.created_at DESC",
            [(int)$id]
        );

        $feedbacks = $this->db->fetchAll(
            "SELECT f.*, u.name AS customer_name
             FROM order_feedbacks f
             LEFT JOIN users u ON u.id = f.customer_id
             WHERE f.order_id = ?
             ORDER BY f.created_at DESC",
            [(int)$id]
        );

        $delivery = $this->db->fetch("SELECT * FROM deliveries WHERE order_id = ? ORDER BY id DESC LIMIT 1", [(int)$id]);

        $payments = $this->db->fetchAll(
            "SELECT p.*, u.name AS confirmed_by_name FROM payments p LEFT JOIN users u ON u.id = p.confirmed_by WHERE p.order_id = ? ORDER BY p.created_at DESC",
            [(int)$id]
        );

        $staffList = $this->db->fetchAll("SELECT id, name FROM users WHERE role='staff' AND is_active=1 ORDER BY name");

        $this->view('admin.orders.show', [
            'pageTitle'  => "Đơn hàng #{$order->order_code}",
            'breadcrumbs'=> ['Dashboard' => url('/admin/dashboard'), 'Đơn hàng' => url('/admin/orders'), $order->order_code => ''],
            'order'      => $order,
            'items'      => $items,
            'steps'      => $steps,
            'statusLogs' => $statusLogs,
            'feedbacks'  => $feedbacks,
            'delivery'   => $delivery,
            'payments'   => $payments,
            'staffList'  => $staffList,
        ]);
    }

    public function confirm(string $id): void
    {
        if (!verify_csrf()) { $this->json(['error' => 'Invalid'], 400); return; }

        $order = $this->db->fetch("SELECT * FROM orders WHERE id = ?", [(int)$id]);
        if (!$order || $order->production_status !== 'pending') {
            $this->redirect("/admin/orders/{$id}", ['error' => 'Không thể xác nhận đơn này']);
            return;
        }

        $this->db->beginTransaction();
        try {
            // Update order status
            $this->db->update('orders', [
                'production_status' => 'confirmed',
                'overall_status'    => 'processing',
            ], 'id = ?', [(int)$id]);

            // Create production steps for each order item
            $items = $this->db->fetchAll("SELECT * FROM order_items WHERE order_id = ?", [(int)$id]);
            $steps = $this->appConfig['production_steps'];

            foreach ($items as $item) {
                foreach ($steps as $stepNum => $step) {
                    $this->db->insert('production_steps', [
                        'order_id'      => (int)$id,
                        'order_item_id' => $item->id,
                        'step_number'   => $stepNum,
                        'step_name'     => $step['name'],
                        'status'        => $stepNum === 1 ? 'waiting' : 'waiting',
                        'created_at'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            // Status log
            $this->db->insert('order_status_logs', [
                'order_id'   => (int)$id,
                'from_status'=> 'pending',
                'to_status'  => 'confirmed',
                'changed_by' => Auth::getInstance()->id(),
                'notes'      => $this->input('notes', 'Đơn hàng được xác nhận và đưa vào sản xuất'),
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            // Notify customer
            $this->notify($order->customer_id, 'order_status',
                "Đơn hàng {$order->order_code} đã được xác nhận",
                "Đơn hàng của bạn đang được đưa vào sản xuất",
                ['order_id' => $id, 'order_code' => $order->order_code]
            );

            $this->db->commit();
            $this->redirect("/admin/orders/{$id}", ['success' => 'Đã xác nhận và tạo công đoạn sản xuất']);

        } catch (\Exception $e) {
            $this->db->rollback();
            $this->redirect("/admin/orders/{$id}", ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function cancel(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/orders/{$id}", ['error' => 'CSRF error']); return; }

        $order = $this->db->fetch("SELECT * FROM orders WHERE id = ?", [(int)$id]);
        if (!$order) { $this->redirect('/admin/orders', ['error' => 'Không tìm thấy đơn']); return; }

        $reason = $this->input('cancel_reason', 'Admin hủy đơn');

        $this->db->update('orders', [
            'overall_status' => 'cancelled',
        ], 'id = ?', [(int)$id]);

        $this->db->insert('order_status_logs', [
            'order_id'   => (int)$id,
            'from_status'=> $order->overall_status,
            'to_status'  => 'cancelled',
            'changed_by' => Auth::getInstance()->id(),
            'notes'      => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->notify($order->customer_id, 'order_status',
            "Đơn hàng {$order->order_code} đã bị hủy",
            $reason,
            ['order_id' => $id]
        );

        $this->logActivity('cancel_order', 'orders', (int)$id, "Hủy đơn hàng: $reason");
        $this->redirect("/admin/orders/{$id}", ['success' => 'Đã hủy đơn hàng']);
    }

    public function updateDeliveryStatus(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/orders/{$id}", ['error' => 'CSRF error']); return; }

        $newStatus = $this->input('delivery_status', '');
        $validStatuses = ['waiting_pickup', 'shipping', 'delivered', 'pending_return', 'returned'];

        if (!in_array($newStatus, $validStatuses)) {
            $this->redirect("/admin/orders/{$id}", ['error' => 'Trạng thái không hợp lệ']);
            return;
        }

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [(int)$id]);
        if (!$order) { $this->redirect('/admin/orders', ['error' => 'Không tìm thấy đơn']); return; }

        $this->db->update('orders', ['delivery_status' => $newStatus], 'id=?', [(int)$id]);

        // Update or create delivery record
        $delivery = $this->db->fetch("SELECT id FROM deliveries WHERE order_id=? ORDER BY id DESC LIMIT 1", [(int)$id]);
        $deliveryData = [
            'status'     => $newStatus,
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($newStatus === 'shipping') {
            $deliveryData['shipped_at'] = date('Y-m-d H:i:s');
        }
        if ($newStatus === 'delivered') {
            $deliveryData['delivered_at'] = date('Y-m-d H:i:s');
        }

        if ($delivery) {
            $this->db->update('deliveries', $deliveryData, 'id=?', [$delivery->id]);
        } else {
            $this->db->insert('deliveries', array_merge($deliveryData, [
                'order_id'   => (int)$id,
                'method'     => $this->input('delivery_method', 'courier'),
                'created_at' => date('Y-m-d H:i:s'),
            ]));
        }

        $this->db->insert('order_status_logs', [
            'order_id'   => (int)$id,
            'from_status'=> $order->delivery_status,
            'to_status'  => $newStatus,
            'status_type'=> 'delivery',
            'changed_by' => Auth::getInstance()->id(),
            'notes'      => $this->input('notes', ''),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify customer
        $statuses = $this->appConfig['delivery_statuses'];
        $label = $statuses[$newStatus]['label'] ?? $newStatus;
        $this->notify($order->customer_id, 'order_status',
            "Đơn hàng {$order->order_code}: {$label}",
            "Trạng thái giao hàng đã được cập nhật",
            ['order_id' => $id]
        );

        $this->redirect("/admin/orders/{$id}", ['success' => "Đã cập nhật: {$label}"]);
    }

    public function approveReturn(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/orders/{$id}", ['error' => 'CSRF error']); return; }

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [(int)$id]);
        if (!$order) { $this->redirect('/admin/orders'); return; }

        $this->db->update('orders', ['delivery_status' => 'returned'], 'id=?', [(int)$id]);
        $this->db->update('deliveries', [
            'status'     => 'returned',
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'order_id=?', [(int)$id]);

        $this->db->insert('order_status_logs', [
            'order_id'   => (int)$id,
            'from_status'=> 'pending_return',
            'to_status'  => 'returned',
            'status_type'=> 'delivery',
            'changed_by' => Auth::getInstance()->id(),
            'notes'      => 'Admin duyệt hoàn trả',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->notify($order->customer_id, 'order_status',
            "Yêu cầu hoàn trả đã được duyệt",
            "Đơn hàng {$order->order_code} đã được chấp nhận hoàn trả",
            ['order_id' => $id]
        );

        $this->redirect("/admin/orders/{$id}", ['success' => 'Đã duyệt hoàn trả']);
    }

    public function edit(string $id): void
    {
        $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [(int)$id]);
        if (!$order) { $this->redirect('/admin/orders', ['error' => 'Không tìm thấy']); return; }

        $items    = $this->db->fetchAll("SELECT oi.*, pt.name AS product_name FROM order_items oi LEFT JOIN product_types pt ON pt.id = oi.product_type_id WHERE oi.order_id=?", [(int)$id]);
        $customers= $this->db->fetchAll("SELECT id, name, clinic_name FROM users WHERE role='customer' AND is_active=1 ORDER BY name");

        $this->view('admin.orders.edit', [
            'pageTitle'   => "Sửa đơn #{$order->order_code}",
            'breadcrumbs' => ['Đơn hàng' => url('/admin/orders'), $order->order_code => url("/admin/orders/{$id}"), 'Sửa' => ''],
            'order'       => $order,
            'items'       => $items,
            'customers'   => $customers,
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/orders/{$id}/edit", ['error' => 'CSRF error']); return; }

        $this->db->update('orders', [
            'due_date'  => $this->input('due_date'),
            'priority'  => $this->input('priority', 'normal'),
            'notes'     => $this->input('notes', ''),
            'discount'  => (float)$this->input('discount', 0),
        ], 'id=?', [(int)$id]);

        $this->logActivity('update_order', 'orders', (int)$id, "Cập nhật đơn hàng");
        $this->redirect("/admin/orders/{$id}", ['success' => 'Đã cập nhật đơn hàng']);
    }
}
