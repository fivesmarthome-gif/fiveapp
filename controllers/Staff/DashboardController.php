<?php
/**
 * Staff Dashboard Controller
 * HoanKiem LAB
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $auth  = Auth::getInstance();
        $staff = $auth->user();

        // Get tasks assigned to this staff
        $pendingSteps = $this->db->fetchAll(
            "SELECT ps.*, o.order_code, o.due_date, o.priority,
                    u.name AS customer_name, u.clinic_name,
                    oi.tooth_numbers, pt.name AS product_name
             FROM production_steps ps
             JOIN orders o ON o.id = ps.order_id
             JOIN order_items oi ON oi.id = ps.order_item_id
             LEFT JOIN product_types pt ON pt.id = oi.product_type_id
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE ps.assigned_to = ? AND ps.status IN ('waiting','in_progress')
               AND o.overall_status NOT IN ('cancelled')
             ORDER BY o.priority DESC, o.due_date ASC",
            [$staff->id]
        );

        $completedToday = $this->db->count(
            'production_steps',
            "assigned_to=? AND status='completed' AND DATE(completed_at)=?",
            [$staff->id, date('Y-m-d')]
        );

        $inProgressCount = $this->db->count(
            'production_steps',
            "assigned_to=? AND status='in_progress'",
            [$staff->id]
        );

        $waitingCount = $this->db->count(
            'production_steps',
            "assigned_to=? AND status='waiting'",
            [$staff->id]
        );

        // Today's appointments
        $appointments = $this->db->fetchAll(
            "SELECT a.*, u.name AS customer_name, u.phone AS customer_phone
             FROM appointments a
             LEFT JOIN users u ON u.id = a.customer_id
             WHERE a.staff_id=? AND DATE(a.appointment_date)=? AND a.status != 'cancelled'
             ORDER BY a.appointment_date",
            [$staff->id, date('Y-m-d')]
        );

        $this->view('staff.dashboard', [
            'pageTitle'      => 'HoanKiem LAB',
            'staff'          => $staff,
            'pendingSteps'   => $pendingSteps,
            'completedToday' => $completedToday,
            'inProgressCount'=> $inProgressCount,
            'waitingCount'   => $waitingCount,
            'appointments'   => $appointments,
        ]);
    }
}
