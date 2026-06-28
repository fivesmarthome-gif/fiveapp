<?php
/**
 * Staff Production Controller
 * HoanKiem LAB
 */

class ProductionController extends Controller
{
    public function index(): void
    {
        $auth  = Auth::getInstance();
        $staff = $auth->user();
        $filter= $this->input('status', '');

        $where  = "ps.assigned_to = ? AND o.overall_status != 'cancelled'";
        $params = [$staff->id];

        if ($filter) {
            $where  .= " AND ps.status = ?";
            $params[] = $filter;
        } else {
            $where .= " AND ps.status IN ('waiting','in_progress')";
        }

        $steps = $this->db->fetchAll(
            "SELECT ps.*, o.order_code, o.due_date, o.priority,
                    u.name AS customer_name, u.clinic_name,
                    oi.tooth_numbers, pt.name AS product_name
             FROM production_steps ps
             JOIN orders o ON o.id = ps.order_id
             JOIN order_items oi ON oi.id = ps.order_item_id
             LEFT JOIN product_types pt ON pt.id = oi.product_type_id
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE {$where}
             ORDER BY o.priority DESC, o.due_date ASC, ps.step_number ASC",
            $params
        );

        $this->view('staff.production.index', [
            'pageTitle' => 'Công việc của tôi',
            'steps'     => $steps,
            'filter'    => $filter,
        ]);
    }

    public function show(string $stepId): void
    {
        $auth  = Auth::getInstance();
        $staff = $auth->user();

        $step = $this->db->fetch(
            "SELECT ps.*, o.order_code, o.due_date, o.priority, o.notes AS order_notes,
                    u.name AS customer_name, u.clinic_name,
                    oi.tooth_numbers, oi.shade, oi.material_type, oi.specifications,
                    pt.name AS product_name
             FROM production_steps ps
             JOIN orders o ON o.id = ps.order_id
             JOIN order_items oi ON oi.id = ps.order_item_id
             LEFT JOIN product_types pt ON pt.id = oi.product_type_id
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE ps.id = ? AND ps.assigned_to = ?",
            [(int)$stepId, $staff->id]
        );

        if (!$step) {
            $this->redirect('/staff/production', ['error' => 'Không tìm thấy công đoạn']);
            return;
        }

        $this->view('staff.production.show', [
            'pageTitle' => "Công đoạn: {$step->step_name}",
            'backUrl'   => url('/staff/production'),
            'step'      => $step,
        ]);
    }

    public function start(string $stepId): void
    {
        if (!verify_csrf()) { $this->redirect('/staff/production'); return; }

        $auth  = Auth::getInstance();
        $staff = $auth->user();

        $step = $this->db->fetch("SELECT * FROM production_steps WHERE id=? AND assigned_to=? AND status='waiting'", [(int)$stepId, $staff->id]);
        if (!$step) { $this->redirect('/staff/production', ['error' => 'Không thể bắt đầu']); return; }

        $this->db->update('production_steps', [
            'status'     => 'in_progress',
            'started_at' => date('Y-m-d H:i:s'),
        ], 'id=?', [(int)$stepId]);

        // Update order production status
        $this->db->update('orders', ['production_status' => 'in_production'], 'id=?', [$step->order_id]);

        $this->redirect("/staff/production/{$stepId}", ['success' => 'Đã bắt đầu công đoạn']);
    }

    public function complete(string $stepId): void
    {
        if (!verify_csrf()) { $this->redirect('/staff/production'); return; }

        $auth  = Auth::getInstance();
        $staff = $auth->user();

        $step = $this->db->fetch("SELECT * FROM production_steps WHERE id=? AND assigned_to=? AND status='in_progress'", [(int)$stepId, $staff->id]);
        if (!$step) { $this->redirect('/staff/production', ['error' => 'Không thể hoàn thành']); return; }

        // Upload step images
        $images = $this->uploadMultipleFiles('step_images', 'steps');

        $this->db->update('production_steps', [
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
            'notes'        => $this->input('notes', ''),
            'step_images'  => json_encode($images),
        ], 'id=?', [(int)$stepId]);

        // Activate next step for same order_item
        $nextStep = $this->db->fetch(
            "SELECT * FROM production_steps WHERE order_item_id=? AND step_number=? AND status='waiting'",
            [$step->order_item_id, $step->step_number + 1]
        );

        if ($nextStep) {
            $this->db->update('production_steps', ['status' => 'waiting'], 'id=?', [$nextStep->id]);
        }

        // Check if ALL steps for this order are done
        $totalSteps     = $this->db->count('production_steps', 'order_id=?', [$step->order_id]);
        $completedSteps = $this->db->count('production_steps', "order_id=? AND status='completed'", [$step->order_id]);

        if ($completedSteps >= $totalSteps) {
            $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [$step->order_id]);
            $this->db->update('orders', [
                'production_status' => 'ready',
                'delivery_status'   => 'waiting_pickup',
            ], 'id=?', [$step->order_id]);

            // Notify admins and customer
            $admins = $this->db->fetchAll("SELECT id FROM users WHERE role='admin' AND is_active=1");
            foreach ($admins as $admin) {
                $this->notify($admin->id, 'order_status',
                    "Đơn hàng {$order->order_code} sản xuất xong",
                    "Đơn hàng đã hoàn thành tất cả công đoạn",
                    ['order_id' => $step->order_id]
                );
            }
        }

        $this->logActivity('complete_step', 'production_steps', (int)$stepId, "Hoàn thành: {$step->step_name}");
        $this->redirect('/staff/production', ['success' => "Đã hoàn thành công đoạn: {$step->step_name}"]);
    }

    public function rework(string $stepId): void
    {
        if (!verify_csrf()) { $this->redirect('/staff/production'); return; }

        $auth = Auth::getInstance();
        $step = $this->db->fetch("SELECT * FROM production_steps WHERE id=? AND assigned_to=?", [(int)$stepId, $auth->id()]);
        if (!$step) { $this->redirect('/staff/production'); return; }

        $reason = trim($this->input('rework_reason', ''));

        $this->db->update('production_steps', [
            'status'        => 'rework',
            'rework_reason' => $reason,
        ], 'id=?', [(int)$stepId]);

        $this->redirect('/staff/production', ['success' => 'Đã báo cáo cần làm lại']);
    }
}
