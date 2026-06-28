<?php
/**
 * Shipper Dashboard Controller
 * HoanKiem LAB
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $shipper = Auth::getInstance()->user();

        $assignedDeliveries = $this->db->fetchAll(
            "SELECT d.*, o.order_code, o.due_date, o.adjusted_due_date, o.customer_id,
                    c.name AS customer_name, c.clinic_name, c.phone AS customer_phone, c.address AS customer_address
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN users c ON c.id = o.customer_id
             WHERE d.delivered_by = ? AND d.status IN ('shipping','waiting_pickup')
             ORDER BY d.updated_at DESC, d.created_at DESC",
            [$shipper->id]
        );

        $availableDeliveries = $this->db->fetchAll(
            "SELECT d.*, o.order_code, o.due_date, o.adjusted_due_date, o.customer_id,
                    c.name AS customer_name, c.clinic_name, c.phone AS customer_phone, c.address AS customer_address
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN users c ON c.id = o.customer_id
             WHERE d.status = 'waiting_pickup' AND (d.delivered_by IS NULL OR d.delivered_by = 0)
             ORDER BY COALESCE(o.adjusted_due_date, o.due_date) ASC, d.created_at ASC"
        );

        $this->view('shipper.dashboard', [
            'pageTitle' => 'Đơn giao hàng',
            'assignedDeliveries' => $assignedDeliveries,
            'availableDeliveries' => $availableDeliveries,
        ]);
    }

    public function show(string $id): void
    {
        $shipper = Auth::getInstance()->user();
        $delivery = $this->getDelivery((int)$id, $shipper->id, true);

        if (!$delivery) {
            $this->redirect('/shipper/dashboard', ['error' => 'Không tìm thấy vận đơn']);
            return;
        }

        $logs = $this->db->fetchAll(
            "SELECT l.*, u.name AS changed_by_name
             FROM order_status_logs l
             LEFT JOIN users u ON u.id = l.changed_by
             WHERE l.order_id = ? AND l.status_type = 'delivery'
             ORDER BY l.created_at DESC
             LIMIT 12",
            [$delivery->order_id]
        );

        $this->view('shipper.show', [
            'pageTitle' => "Giao {$delivery->order_code}",
            'backUrl' => url('/shipper/dashboard'),
            'delivery' => $delivery,
            'logs' => $logs,
        ]);
    }

    public function accept(string $id): void
    {
        if (!verify_csrf()) {
            $this->redirect('/shipper/dashboard', ['error' => 'CSRF error']);
            return;
        }

        $shipper = Auth::getInstance()->user();
        $delivery = $this->getDelivery((int)$id, null, false);

        if (!$delivery || $delivery->status !== 'waiting_pickup' || $delivery->delivered_by) {
            $this->redirect('/shipper/dashboard', ['error' => 'Vận đơn đã được nhận bởi shipper khác']);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();
        try {
            $this->db->update('deliveries', [
                'delivered_by' => $shipper->id,
                'status' => 'shipping',
                'shipped_at' => $delivery->shipped_at ?: $now,
                'updated_at' => $now,
            ], 'id=?', [$delivery->id]);

            $this->db->update('orders', ['delivery_status' => 'shipping'], 'id=?', [$delivery->order_id]);

            $this->db->insert('order_status_logs', [
                'order_id' => $delivery->order_id,
                'from_status' => $delivery->status,
                'to_status' => 'shipping',
                'status_type' => 'delivery',
                'changed_by' => $shipper->id,
                'notes' => "Shipper {$shipper->name} đã nhận giao đơn hàng",
                'created_at' => $now,
            ]);

            $this->notify($delivery->customer_id, 'order_status',
                "Shipper đang giao đơn {$delivery->order_code}",
                "Đơn hàng đang trên đường giao. Vui lòng chuẩn bị nhận hàng.",
                ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id]
            );

            $admins = $this->db->fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
            foreach ($admins as $admin) {
                $this->notify($admin->id, 'order_status',
                    "Shipper đã nhận đơn {$delivery->order_code}",
                    "{$shipper->name} đang giao hàng cho {$delivery->clinic_name}",
                    ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id]
                );
            }

            $this->db->commit();
            $this->redirect("/shipper/deliveries/{$delivery->id}", ['success' => 'Đã nhận đơn và báo khách chuẩn bị nhận hàng']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect('/shipper/dashboard', ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function updateLocation(string $id): void
    {
        if (!verify_csrf()) {
            $this->redirect("/shipper/deliveries/{$id}", ['error' => 'CSRF error']);
            return;
        }

        $shipper = Auth::getInstance()->user();
        $delivery = $this->getDelivery((int)$id, $shipper->id, false);

        if (!$delivery || !in_array($delivery->status, ['waiting_pickup', 'shipping'])) {
            $this->redirect('/shipper/dashboard', ['error' => 'Không thể cập nhật vị trí cho vận đơn này']);
            return;
        }

        $lat = $this->input('shipper_lat');
        $lng = $this->input('shipper_lng');
        $note = trim($this->input('shipper_location_note', ''));

        if (!is_numeric($lat) || !is_numeric($lng)) {
            $this->redirect("/shipper/deliveries/{$id}", ['error' => 'Vui lòng lấy vị trí GPS hoặc nhập tọa độ hợp lệ']);
            return;
        }

        $lat = max(-90, min(90, (float)$lat));
        $lng = max(-180, min(180, (float)$lng));
        $now = date('Y-m-d H:i:s');

        $this->db->update('deliveries', [
            'shipper_lat' => $lat,
            'shipper_lng' => $lng,
            'shipper_location_note' => $note,
            'shipper_location_updated_at' => $now,
            'updated_at' => $now,
        ], 'id=? AND delivered_by=?', [$delivery->id, $shipper->id]);

        $this->db->insert('order_status_logs', [
            'order_id' => $delivery->order_id,
            'from_status' => $delivery->status,
            'to_status' => $delivery->status,
            'status_type' => 'delivery',
            'changed_by' => $shipper->id,
            'notes' => 'Shipper cập nhật vị trí' . ($note ? ": {$note}" : ''),
            'created_at' => $now,
        ]);

        $this->notify($delivery->customer_id, 'order_status',
            "Cập nhật vị trí shipper - {$delivery->order_code}",
            $note ?: 'Shipper vừa cập nhật vị trí giao hàng.',
            ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id]
        );

        $this->redirect("/shipper/deliveries/{$id}", ['success' => 'Đã cập nhật vị trí']);
    }

    public function markDelivered(string $id): void
    {
        if (!verify_csrf()) {
            $this->redirect("/shipper/deliveries/{$id}", ['error' => 'CSRF error']);
            return;
        }

        $shipper = Auth::getInstance()->user();
        $delivery = $this->getDelivery((int)$id, $shipper->id, false);

        if (!$delivery || $delivery->status !== 'shipping') {
            $this->redirect('/shipper/dashboard', ['error' => 'Không thể hoàn tất vận đơn này']);
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->db->beginTransaction();
        try {
            $this->db->update('deliveries', [
                'status' => 'delivered',
                'delivered_at' => $now,
                'updated_at' => $now,
            ], 'id=? AND delivered_by=?', [$delivery->id, $shipper->id]);

            $this->db->update('orders', ['delivery_status' => 'delivered'], 'id=?', [$delivery->order_id]);

            $this->db->insert('order_status_logs', [
                'order_id' => $delivery->order_id,
                'from_status' => 'shipping',
                'to_status' => 'delivered',
                'status_type' => 'delivery',
                'changed_by' => $shipper->id,
                'notes' => 'Shipper xác nhận đã giao hàng',
                'created_at' => $now,
            ]);

            $this->notify($delivery->customer_id, 'order_status',
                "Đơn {$delivery->order_code} đã giao tới nơi",
                "Vui lòng kiểm tra và xác nhận đã nhận hàng trong hệ thống.",
                ['order_id' => $delivery->order_id, 'delivery_id' => $delivery->id]
            );

            $this->db->commit();
            $this->redirect("/shipper/deliveries/{$id}", ['success' => 'Đã xác nhận giao hàng']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect("/shipper/deliveries/{$id}", ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    private function getDelivery(int $id, ?int $shipperId = null, bool $allowAvailable = false): ?object
    {
        $where = "d.id = ?";
        $params = [$id];

        if ($shipperId !== null) {
            if ($allowAvailable) {
                $where .= " AND (d.delivered_by = ? OR d.delivered_by IS NULL OR d.delivered_by = 0)";
            } else {
                $where .= " AND d.delivered_by = ?";
            }
            $params[] = $shipperId;
        }

        return $this->db->fetch(
            "SELECT d.*, o.order_code, o.due_date, o.adjusted_due_date, o.customer_id,
                    c.name AS customer_name, c.clinic_name, c.phone AS customer_phone, c.address AS customer_address
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN users c ON c.id = o.customer_id
             WHERE {$where}",
            $params
        );
    }
}
