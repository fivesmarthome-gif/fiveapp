<?php
/**
 * Admin Delivery Controller
 * HoanKiem LAB
 */

class DeliveryController extends Controller
{
    public function index(): void
    {
        $status = $this->input('status', '');
        $where = "1=1";
        $params = [];
        if ($status) {
            $where .= " AND d.status = ?";
            $params = [$status];
        }

        $deliveries = $this->db->fetchAll(
            "SELECT d.*, o.order_code, o.due_date, o.adjusted_due_date,
                    c.id AS customer_id, c.name AS customer_name, c.clinic_name,
                    s.name AS shipper_name, s.phone AS shipper_phone
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN users c ON c.id = o.customer_id
             LEFT JOIN users s ON s.id = d.delivered_by
             WHERE {$where}
             ORDER BY d.created_at DESC",
            $params
        );

        $this->view('admin.deliveries.index', [
            'pageTitle' => 'Quản lý giao hàng',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Giao hàng' => ''],
            'deliveries' => $deliveries,
            'filterStatus' => $status
        ]);
    }

    public function show(string $id): void
    {
        $delivery = $this->db->fetch(
            "SELECT d.*, o.order_code, o.due_date, o.adjusted_due_date, o.production_status, o.delivery_status,
                    o.created_at AS order_created_at, o.overall_status,
                    c.name AS customer_name, c.phone AS customer_phone, c.clinic_name,
                    c.address AS customer_address, c.id AS customer_id,
                    s.name AS shipper_name, s.phone AS shipper_phone
             FROM deliveries d
             JOIN orders o ON o.id = d.order_id
             JOIN users c ON c.id = o.customer_id
             LEFT JOIN users s ON s.id = d.delivered_by
             WHERE d.id = ?",
            [(int)$id]
        );

        if (!$delivery) { $this->redirect('/admin/deliveries'); return; }

        $staffList = $this->db->fetchAll("SELECT id, name, role FROM users WHERE role IN ('staff','shipper') AND is_active=1 ORDER BY role, name ASC");

        // Fetch all status logs for this order (delivery-related)
        $deliveryLogs = $this->db->fetchAll(
            "SELECT l.*, u.name AS changed_by_name
             FROM order_status_logs l
             LEFT JOIN users u ON u.id = l.changed_by
             WHERE l.order_id = ? AND (l.status_type = 'delivery' OR l.status_type = 'overall')
             ORDER BY l.created_at ASC",
            [$delivery->order_id]
        );

        // Fetch all order logs including production milestones
        $allLogs = $this->db->fetchAll(
            "SELECT l.*, u.name AS changed_by_name
             FROM order_status_logs l
             LEFT JOIN users u ON u.id = l.changed_by
             WHERE l.order_id = ?
             ORDER BY l.created_at DESC
             LIMIT 20",
            [$delivery->order_id]
        );

        $this->view('admin.deliveries.show', [
            'pageTitle'    => "Vận đơn #{$delivery->order_code}",
            'breadcrumbs'  => ['Giao hàng' => url('/admin/deliveries'), $delivery->order_code => ''],
            'delivery'     => $delivery,
            'staffList'    => $staffList,
            'deliveryLogs' => $deliveryLogs,
            'allLogs'      => $allLogs,
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/deliveries/{$id}"); return; }

        $delivery = $this->db->fetch("SELECT * FROM deliveries WHERE id = ?", [(int)$id]);
        if (!$delivery) { $this->redirect('/admin/deliveries'); return; }

        $status = $this->input('status');
        $deliveredBy = $this->input('delivered_by') ?: null;
        $courierName = $this->input('courier_name', '');
        $trackingNumber = $this->input('tracking_number', '');
        $notes = $this->input('notes', '');

        // Upload proof photo if provided
        $proofPhoto = $this->uploadFile('proof_photo', 'deliveries');

        $updateData = [
            'status'          => $status,
            'delivered_by'    => $deliveredBy,
            'courier_name'    => $courierName,
            'tracking_number' => $trackingNumber,
            'notes'           => $notes,
            'updated_at'      => date('Y-m-d H:i:s')
        ];

        if ($proofPhoto) {
            $updateData['proof_photo'] = $proofPhoto;
        }

        // Set timestamps based on status transitions
        if ($status === 'shipping' && !$delivery->shipped_at) {
            $updateData['shipped_at'] = date('Y-m-d H:i:s');
        }
        if ($status === 'delivered' && !$delivery->delivered_at) {
            $updateData['delivered_at'] = date('Y-m-d H:i:s');
        }

        $this->db->beginTransaction();
        try {
            $this->db->update('deliveries', $updateData, 'id = ?', [$delivery->id]);

            // Sync with orders table
            $this->db->update('orders', ['delivery_status' => $status], 'id = ?', [$delivery->order_id]);

            // Save order log
            $this->db->insert('order_status_logs', [
                'order_id' => $delivery->order_id,
                'from_status' => $delivery->status,
                'to_status' => $status,
                'status_type' => 'delivery',
                'changed_by' => Auth::getInstance()->id(),
                'notes' => "Cập nhật thông tin giao nhận: " . $notes,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Notify customer
            $order = $this->db->fetch("SELECT customer_id, order_code FROM orders WHERE id=?", [$delivery->order_id]);
            $statuses = $this->appConfig['delivery_statuses'];
            $statusLabel = $statuses[$status]['label'] ?? $status;

            $this->notify($order->customer_id, 'order_status',
                "Đơn hàng {$order->order_code} - {$statusLabel}",
                "Thông tin giao hàng đã thay đổi",
                ['order_id' => $delivery->order_id]
            );

            $this->db->commit();
            $this->redirect("/admin/deliveries/{$id}", ['success' => 'Cập nhật giao hàng thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect("/admin/deliveries/{$id}", ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
