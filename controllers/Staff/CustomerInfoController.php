<?php
/**
 * Staff Customer Info Controller - HoanKiem LAB
 */
class CustomerInfoController extends Controller
{
    public function index(): void
    {
        $search = trim($this->input('search', ''));
        $where  = "role='customer' AND is_active=1";
        $params = [];
        if ($search) { $where .= " AND (name LIKE ? OR clinic_name LIKE ? OR phone LIKE ?)"; $params = ["%$search%","%$search%","%$search%"]; }
        $customers = $this->db->fetchAll("SELECT * FROM users WHERE {$where} ORDER BY name", $params);
        $this->view('staff.customers', ['pageTitle' => 'Khách hàng', 'customers' => $customers, 'search' => $search]);
    }
    public function show(string $id): void
    {
        $user = $this->db->fetch("SELECT * FROM users WHERE id=? AND role='customer'", [(int)$id]);
        if (!$user) { $this->redirect('/staff/customers'); return; }
        $orders = $this->db->fetchAll("SELECT * FROM orders WHERE customer_id=? ORDER BY created_at DESC LIMIT 10", [(int)$id]);
        $this->view('staff.customer_show', ['pageTitle' => $user->name, 'backUrl' => url('/staff/customers'), 'user' => $user, 'orders' => $orders]);
    }
}
