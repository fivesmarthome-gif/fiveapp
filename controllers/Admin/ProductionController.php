<?php
/**
 * Admin Production Controller
 * HoanKiem LAB
 */

class ProductionController extends Controller
{
    public function index(): void
    {
        $status = $this->input('status', '');
        $where  = "o.production_status IN ('confirmed','in_production')";
        $params = [];

        if ($status) {
            $where  = "o.production_status = ?";
            $params = [$status];
        }

        $orders = $this->db->fetchAll(
            "SELECT o.*, u.name AS customer_name, u.clinic_name,
                    COUNT(ps.id) AS total_steps,
                    SUM(CASE WHEN ps.status='completed' THEN 1 ELSE 0 END) AS completed_steps
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             LEFT JOIN production_steps ps ON ps.order_id = o.id
             WHERE {$where}
             GROUP BY o.id
             ORDER BY o.priority DESC, o.due_date ASC",
            $params
        );

        $this->view('admin.production.index', [
            'pageTitle'   => 'Quản lý sản xuất',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Sản xuất' => ''],
            'orders'      => $orders,
            'filterStatus'=> $status,
        ]);
    }

    public function show(string $orderId): void
    {
        $order = $this->db->fetch(
            "SELECT o.*, u.name AS customer_name FROM orders o LEFT JOIN users u ON u.id=o.customer_id WHERE o.id=?",
            [(int)$orderId]
        );
        if (!$order) { $this->redirect('/admin/production', ['error' => 'Không tìm thấy']); return; }

        $steps = $this->db->fetchAll(
            "SELECT ps.*, u.name AS staff_name, oi.tooth_numbers, pt.name AS product_name
             FROM production_steps ps
             LEFT JOIN users u ON u.id=ps.assigned_to
             LEFT JOIN order_items oi ON oi.id=ps.order_item_id
             LEFT JOIN product_types pt ON pt.id=oi.product_type_id
             WHERE ps.order_id=?
             ORDER BY ps.order_item_id, ps.step_number",
            [(int)$orderId]
        );

        $staffList = $this->db->fetchAll("SELECT id, name FROM users WHERE role='staff' AND is_active=1 ORDER BY name");

        $this->view('admin.production.show', [
            'pageTitle'  => "Sản xuất: {$order->order_code}",
            'breadcrumbs'=> ['Sản xuất' => url('/admin/production'), $order->order_code => ''],
            'order'      => $order,
            'steps'      => $steps,
            'staffList'  => $staffList,
        ]);
    }

    public function assign(string $stepId): void
    {
        if (!verify_csrf()) { $this->json(['error' => 'CSRF'], 400); return; }

        $step = $this->db->fetch("SELECT * FROM production_steps WHERE id=?", [(int)$stepId]);
        if (!$step) { $this->redirect('/admin/production', ['error' => 'Không tìm thấy công đoạn']); return; }

        $this->db->update('production_steps', [
            'assigned_to' => $this->input('staff_id') ?: null,
        ], 'id=?', [(int)$stepId]);

        $this->redirect("/admin/production/{$step->order_id}", ['success' => 'Đã phân công nhân viên']);
    }

    public function qcPass(string $stepId): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/production'); return; }

        $step = $this->db->fetch("SELECT * FROM production_steps WHERE id=?", [(int)$stepId]);
        if (!$step) { $this->redirect('/admin/production'); return; }

        // Mark order as QC passed
        $this->db->update('orders', [
            'production_status' => 'qc_passed',
        ], 'id=?', [$step->order_id]);

        // Check if all steps done -> set to ready
        $totalSteps = $this->db->count('production_steps', 'order_id=?', [$step->order_id]);
        $completedSteps = $this->db->count('production_steps', "order_id=? AND status='completed'", [$step->order_id]);

        if ($completedSteps >= $totalSteps) {
            $this->db->update('orders', ['production_status' => 'ready', 'delivery_status' => 'waiting_pickup'], 'id=?', [$step->order_id]);

            $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [$step->order_id]);
            $this->notify($order->customer_id, 'order_status',
                "Đơn hàng {$order->order_code} sẵn sàng giao",
                "Đơn hàng của bạn đã hoàn thành sản xuất và đang chờ giao",
                ['order_id' => $step->order_id]
            );
        }

        $this->redirect("/admin/production/{$step->order_id}", ['success' => 'QC đạt']);
    }
}
