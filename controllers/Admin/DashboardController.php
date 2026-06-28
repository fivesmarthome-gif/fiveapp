<?php
/**
 * Admin Dashboard Controller
 * HoanKiem LAB
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $today = date('Y-m-d');
        $thisMonth = date('Y-m');
        $thisYear  = date('Y');

        // Order stats
        $stats = [
            'new_orders'      => $this->db->count('orders', "overall_status = 'new'"),
            'in_production'   => $this->db->count('orders', "production_status = 'in_production'"),
            'ready_to_ship'   => $this->db->count('orders', "production_status = 'ready' AND delivery_status = 'waiting_pickup'"),
            'shipping'        => $this->db->count('orders', "delivery_status = 'shipping'"),
            'overdue'         => $this->db->count('orders', "due_date < ? AND overall_status NOT IN ('completed','cancelled')", [$today]),
            'pending_feedback'=> $this->db->count('order_feedbacks', "status = 'pending'"),
            'low_stock'       => $this->db->count('materials', "current_stock <= min_stock"),
        ];

        // Revenue this month
        $revenueMonth = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed' AND DATE_FORMAT(paid_at,'%Y-%m') = ?",
            [$thisMonth]
        );

        // Revenue this year
        $revenueYear = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed' AND YEAR(paid_at) = ?",
            [$thisYear]
        );

        // Recent orders (10)
        $recentOrders = $this->db->fetchAll(
            "SELECT o.*, u.name AS customer_name, u.clinic_name
             FROM orders o
             LEFT JOIN users u ON u.id = o.customer_id
             ORDER BY o.created_at DESC LIMIT 10"
        );

        // Today's appointments
        $todayAppointments = $this->db->fetchAll(
            "SELECT a.*, u.name AS customer_name, s.name AS staff_name
             FROM appointments a
             LEFT JOIN users u ON u.id = a.customer_id
             LEFT JOIN users s ON s.id = a.staff_id
             WHERE DATE(a.appointment_date) = ?
             AND a.status != 'cancelled'
             ORDER BY a.appointment_date ASC",
            [$today]
        );

        // Revenue last 7 days for chart
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $label = date('d/m', strtotime($date));
            $revenue = (float) $this->db->fetchColumn(
                "SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='confirmed' AND DATE(paid_at) = ?",
                [$date]
            );
            $orders = $this->db->count('orders', "DATE(created_at) = ?", [$date]);
            $chartData[] = [
                'date'    => $label,
                'revenue' => $revenue,
                'orders'  => $orders,
            ];
        }

        $this->view('admin.dashboard', [
            'pageTitle'         => 'Dashboard',
            'stats'             => $stats,
            'revenueMonth'      => $revenueMonth,
            'revenueYear'       => $revenueYear,
            'recentOrders'      => $recentOrders,
            'todayAppointments' => $todayAppointments,
            'chartData'         => $chartData,
        ]);
    }
}
