<?php
/**
 * Staff Appointment Controller - HoanKiem LAB
 */
class AppointmentController extends Controller
{
    public function index(): void
    {
        $auth = Auth::getInstance();
        $appointments = $this->db->fetchAll(
            "SELECT a.*, u.name AS customer_name, u.phone AS customer_phone, u.clinic_name
             FROM appointments a
             LEFT JOIN users u ON u.id = a.customer_id
             WHERE a.staff_id=? AND a.status != 'cancelled'
             ORDER BY a.appointment_date DESC LIMIT 30",
            [$auth->id()]
        );
        $this->view('staff.appointments', [
            'pageTitle'    => 'Lịch hẹn',
            'appointments' => $appointments,
        ]);
    }
}
