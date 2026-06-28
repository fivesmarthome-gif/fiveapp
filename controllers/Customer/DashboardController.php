<?php
/**
 * Customer Dashboard Controller
 * HoanKiem LAB
 */

class DashboardController extends Controller
{
    public function index(): void
    {
        $auth     = Auth::getInstance();
        $customer = $auth->user();

        // Recent orders
        $recentOrders = $this->db->fetchAll(
            "SELECT o.* FROM orders o WHERE o.customer_id=? ORDER BY o.created_at DESC LIMIT 5",
            [$customer->id]
        );

        // Stats
        $totalOrders = $this->db->count('orders', 'customer_id=?', [$customer->id]);
        $totalSpent  = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.customer_id=? AND p.status='confirmed'",
            [$customer->id]
        );
        $activeOrders = $this->db->count('orders', "customer_id=? AND overall_status NOT IN ('completed','cancelled')", [$customer->id]);

        // Articles & Promotions for carousel
        $articles   = $this->db->fetchAll("SELECT * FROM articles WHERE is_published=1 ORDER BY published_at DESC LIMIT 5");
        $promotions = $this->db->fetchAll("SELECT * FROM promotions WHERE is_active=1 AND start_date<=NOW() AND end_date>=NOW() ORDER BY id DESC LIMIT 5");

        $this->view('customer.dashboard', [
            'pageTitle'   => 'HoanKiem LAB',
            'customer'    => $customer,
            'recentOrders'=> $recentOrders,
            'totalOrders' => $totalOrders,
            'totalSpent'  => $totalSpent,
            'activeOrders'=> $activeOrders,
            'articles'    => $articles,
            'promotions'  => $promotions,
        ]);
    }
}
