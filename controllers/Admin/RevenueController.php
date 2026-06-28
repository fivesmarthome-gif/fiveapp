<?php
/**
 * Admin Revenue Controller
 * HoanKiem LAB
 */

class RevenueController extends Controller
{
    public function index(): void
    {
        $year = $this->input('year', date('Y'));
        $month = $this->input('month', '');

        $where = "p.status='confirmed' AND YEAR(p.paid_at) = ?";
        $params = [$year];

        if ($month) {
            $where .= " AND MONTH(p.paid_at) = ?";
            $params[] = $month;
        }

        $payments = $this->db->fetchAll(
            "SELECT p.*, o.order_code, c.name AS customer_name, c.clinic_name
             FROM payments p
             LEFT JOIN orders o ON o.id = p.order_id
             LEFT JOIN users c ON c.id = p.customer_id
             WHERE {$where}
             ORDER BY p.paid_at DESC",
            $params
        );

        $totalRevenue = (float)$this->db->fetchColumn(
            "SELECT SUM(p.amount) FROM payments p WHERE {$where}",
            $params
        );

        // Group by month for chart/summary
        $monthlySummary = $this->db->fetchAll(
            "SELECT MONTH(p.paid_at) AS m, SUM(p.amount) AS total
             FROM payments p
             WHERE p.status='confirmed' AND YEAR(p.paid_at) = ?
             GROUP BY MONTH(p.paid_at)
             ORDER BY m ASC",
            [$year]
        );

        $this->view('admin.revenue.index', [
            'pageTitle' => 'Thống kê doanh thu',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Doanh thu' => ''],
            'payments' => $payments,
            'totalRevenue' => $totalRevenue,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'monthlySummary' => $monthlySummary
        ]);
    }

    public function byCustomer(): void
    {
        $customersRevenue = $this->db->fetchAll(
            "SELECT c.id, c.name, c.clinic_name, c.phone,
                    COUNT(o.id) AS total_orders,
                    COALESCE(SUM(o.total_amount - o.discount), 0) AS order_value,
                    COALESCE(SUM(o.paid_amount), 0) AS paid_value
             FROM users c
             LEFT JOIN orders o ON o.customer_id = c.id
             WHERE c.role='customer'
             GROUP BY c.id
             ORDER BY order_value DESC"
        );

        $this->view('admin.revenue.by_customer', [
            'pageTitle' => 'Doanh thu theo khách hàng',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Doanh thu' => url('/admin/revenue'), 'Khách hàng' => ''],
            'customersRevenue' => $customersRevenue
        ]);
    }
}
