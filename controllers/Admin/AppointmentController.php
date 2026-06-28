<?php
/**
 * Admin Appointment Controller
 * HoanKiem LAB
 */

class AppointmentController extends Controller
{
    public function index(): void
    {
        $appointments = $this->db->fetchAll(
            "SELECT a.*, c.name AS customer_name, c.clinic_name, s.name AS staff_name
             FROM appointments a
             LEFT JOIN users c ON c.id = a.customer_id
             LEFT JOIN users s ON s.id = a.staff_id
             ORDER BY a.appointment_date DESC"
        );

        $this->view('admin.appointments.index', [
            'pageTitle' => 'Quản lý lịch hẹn',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Lịch hẹn' => ''],
            'appointments' => $appointments
        ]);
    }

    public function create(): void
    {
        $customers = $this->db->fetchAll("SELECT id, name, clinic_name FROM users WHERE role='customer' AND is_active=1 ORDER BY name ASC");
        $staffList = $this->db->fetchAll("SELECT id, name FROM users WHERE role='staff' AND is_active=1 ORDER BY name ASC");
        $orders = $this->db->fetchAll("SELECT id, order_code FROM orders WHERE overall_status != 'completed' AND overall_status != 'cancelled' ORDER BY id DESC");

        $this->view('admin.appointments.create', [
            'pageTitle' => 'Đặt lịch hẹn mới',
            'breadcrumbs' => ['Lịch hẹn' => url('/admin/appointments'), 'Đặt lịch' => ''],
            'customers' => $customers,
            'staffList' => $staffList,
            'orders' => $orders
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/appointments/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'customer_id' => 'required',
            'appointment_date' => 'required|date',
            'type' => 'required',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/appointments/create');
            return;
        }

        $id = $this->db->insert('appointments', [
            'customer_id' => (int)$this->input('customer_id'),
            'staff_id' => $this->input('staff_id') ?: null,
            'order_id' => $this->input('order_id') ?: null,
            'branch_id' => $this->input('branch_id') ?: null,
            'appointment_date' => $this->input('appointment_date'),
            'type' => $this->input('type'),
            'status' => 'scheduled',
            'notes' => $this->input('notes', ''),
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Notify customer and staff
        $custName = $this->db->fetchColumn("SELECT name FROM users WHERE id=?", [(int)$this->input('customer_id')]);
        if ($this->input('staff_id')) {
            $this->notify((int)$this->input('staff_id'), 'appointment',
                "Lịch hẹn mới được phân công",
                "Hẹn gặp khách hàng {$custName} lúc " . format_datetime($this->input('appointment_date')),
                ['appointment_id' => $id]
            );
        }
        $this->notify((int)$this->input('customer_id'), 'appointment',
            "Lịch hẹn của bạn đã được lên lịch",
            "Lịch hẹn vào lúc " . format_datetime($this->input('appointment_date')),
            ['appointment_id' => $id]
        );

        $this->redirect('/admin/appointments', ['success' => 'Đã tạo lịch hẹn thành công']);
    }

    public function edit(string $id): void
    {
        $appointment = $this->db->fetch("SELECT * FROM appointments WHERE id = ?", [(int)$id]);
        if (!$appointment) { $this->redirect('/admin/appointments'); return; }

        $customers = $this->db->fetchAll("SELECT id, name, clinic_name FROM users WHERE role='customer' AND is_active=1 ORDER BY name ASC");
        $staffList = $this->db->fetchAll("SELECT id, name FROM users WHERE role='staff' AND is_active=1 ORDER BY name ASC");
        $orders = $this->db->fetchAll("SELECT id, order_code FROM orders WHERE customer_id = ? ORDER BY id DESC", [$appointment->customer_id]);

        $this->view('admin.appointments.edit', [
            'pageTitle' => 'Chỉnh sửa lịch hẹn',
            'breadcrumbs' => ['Lịch hẹn' => url('/admin/appointments'), 'Sửa' => ''],
            'appointment' => $appointment,
            'customers' => $customers,
            'staffList' => $staffList,
            'orders' => $orders
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/appointments/{$id}/edit"); return; }

        $this->db->update('appointments', [
            'staff_id' => $this->input('staff_id') ?: null,
            'order_id' => $this->input('order_id') ?: null,
            'appointment_date' => $this->input('appointment_date'),
            'type' => $this->input('type'),
            'status' => $this->input('status', 'scheduled'),
            'notes' => $this->input('notes', ''),
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [(int)$id]);

        $this->redirect('/admin/appointments', ['success' => 'Đã cập nhật lịch hẹn']);
    }

    public function destroy(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/appointments'); return; }

        $this->db->update('appointments', ['status' => 'cancelled'], 'id = ?', [(int)$id]);
        $this->redirect('/admin/appointments', ['success' => 'Đã huỷ lịch hẹn']);
    }
}
