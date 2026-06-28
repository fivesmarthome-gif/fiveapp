<?php
/**
 * Admin User Controller
 * HoanKiem LAB
 */

class UserController extends Controller
{
    // ============================================
    // CUSTOMERS
    // ============================================

    public function customers(): void
    {
        $page   = max(1, (int)$this->input('page', 1));
        $search = trim($this->input('search', ''));

        $where  = "role = 'customer'";
        $params = [];

        if ($search) {
            $where  .= " AND (name LIKE ? OR clinic_name LIKE ? OR phone LIKE ? OR dentist_name LIKE ?)";
            $params  = ["%$search%", "%$search%", "%$search%", "%$search%"];
        }

        $sql    = "SELECT * FROM users WHERE {$where} ORDER BY created_at DESC";
        $result = $this->db->paginate($sql, $params, $page, 20);

        $this->view('admin.users.customers', [
            'pageTitle'  => 'Quản lý khách hàng',
            'breadcrumbs'=> ['Dashboard' => url('/admin/dashboard'), 'Khách hàng' => ''],
            'users'      => $result['data'],
            'pagination' => $result,
            'search'     => $search,
        ]);
    }

    public function createCustomer(): void
    {
        $branches = $this->db->fetchAll("SELECT * FROM branches WHERE is_active=1 ORDER BY name");

        $this->view('admin.users.create_customer', [
            'pageTitle'  => 'Tạo khách hàng',
            'breadcrumbs'=> ['Khách hàng' => url('/admin/customers'), 'Tạo mới' => ''],
            'branches'   => $branches,
        ]);
    }

    public function storeCustomer(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/customers/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'phone'    => 'required|phone|unique:users,phone',
            'name'     => 'required|min:2',
            'password' => 'required|min:6',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/customers/create');
            return;
        }

        $activationCode = strtoupper(bin2hex(random_bytes(4)));

        $id = Auth::getInstance()->register([
            'phone'           => $this->input('phone'),
            'name'            => $this->input('name'),
            'password'        => $this->input('password'),
            'role'            => 'customer',
            'clinic_name'     => $this->input('clinic_name', ''),
            'dentist_name'    => $this->input('dentist_name', ''),
            'email'           => $this->input('email', ''),
            'address'         => $this->input('address', ''),
            'tax_code'        => $this->input('tax_code', ''),
            'activation_code' => $activationCode,
            'is_active'       => 1,
            'notify_promotion'=> 1,
        ]);

        $this->logActivity('create_customer', 'users', $id, "Tạo khách hàng mới");
        $this->redirect('/admin/customers', ['success' => "Đã tạo khách hàng. Mã kích hoạt: {$activationCode}"]);
    }

    public function showCustomer(string $id): void
    {
        $user = $this->db->fetch("SELECT * FROM users WHERE id=? AND role='customer'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/customers', ['error' => 'Không tìm thấy']); return; }

        $orders = $this->db->fetchAll(
            "SELECT * FROM orders WHERE customer_id=? ORDER BY created_at DESC LIMIT 20",
            [(int)$id]
        );

        $totalRevenue = $this->db->fetchColumn(
            "SELECT COALESCE(SUM(p.amount),0) FROM payments p WHERE p.customer_id=? AND p.status='confirmed'",
            [(int)$id]
        );

        $this->view('admin.users.show_customer', [
            'pageTitle'    => $user->name,
            'breadcrumbs'  => ['Khách hàng' => url('/admin/customers'), $user->name => ''],
            'user'         => $user,
            'orders'       => $orders,
            'totalRevenue' => $totalRevenue,
        ]);
    }

    public function editCustomer(string $id): void
    {
        $user = $this->db->fetch("SELECT * FROM users WHERE id=? AND role='customer'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/customers'); return; }

        $this->view('admin.users.edit_customer', [
            'pageTitle'  => "Sửa: {$user->name}",
            'breadcrumbs'=> ['Khách hàng' => url('/admin/customers'), $user->name => url("/admin/customers/{$id}"), 'Sửa' => ''],
            'user'       => $user,
        ]);
    }

    public function updateCustomer(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/customers/{$id}/edit", ['error' => 'CSRF error']); return; }

        $this->db->update('users', [
            'name'         => $this->input('name'),
            'clinic_name'  => $this->input('clinic_name', ''),
            'dentist_name' => $this->input('dentist_name', ''),
            'email'        => $this->input('email', ''),
            'address'      => $this->input('address', ''),
            'tax_code'     => $this->input('tax_code', ''),
        ], 'id=?', [(int)$id]);

        if ($newPass = $this->input('new_password')) {
            Auth::getInstance()->updatePassword((int)$id, $newPass);
        }

        $this->redirect("/admin/customers/{$id}", ['success' => 'Đã cập nhật thông tin']);
    }

    public function toggleCustomer(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/customers', ['error' => 'CSRF error']); return; }

        $user = $this->db->fetch("SELECT id, is_active FROM users WHERE id=? AND role='customer'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/customers'); return; }

        $this->db->update('users', ['is_active' => $user->is_active ? 0 : 1], 'id=?', [(int)$id]);
        $msg = $user->is_active ? 'Đã khoá tài khoản' : 'Đã mở khoá tài khoản';
        $this->redirect('/admin/customers', ['success' => $msg]);
    }

    // ============================================
    // STAFF
    // ============================================

    public function staff(): void
    {
        $search = trim($this->input('search', ''));
        $where  = "role = 'staff'";
        $params = [];

        if ($search) {
            $where  .= " AND (name LIKE ? OR phone LIKE ?)";
            $params  = ["%$search%", "%$search%"];
        }

        $users = $this->db->fetchAll("SELECT u.*, b.name AS branch_name FROM users u LEFT JOIN branches b ON b.id = u.branch_id WHERE {$where} ORDER BY u.name", $params);

        $branches = $this->db->fetchAll("SELECT * FROM branches WHERE is_active=1 ORDER BY name");
        $steps    = $this->appConfig['production_steps'];

        $this->view('admin.users.staff', [
            'pageTitle'  => 'Quản lý nhân viên',
            'breadcrumbs'=> ['Dashboard' => url('/admin/dashboard'), 'Nhân viên' => ''],
            'users'      => $users,
            'search'     => $search,
            'branches'   => $branches,
            'steps'      => $steps,
        ]);
    }

    public function createStaff(): void
    {
        $branches = $this->db->fetchAll("SELECT * FROM branches WHERE is_active=1 ORDER BY name");
        $steps    = $this->appConfig['production_steps'];

        $this->view('admin.users.create_staff', [
            'pageTitle'  => 'Tạo nhân viên',
            'breadcrumbs'=> ['Nhân viên' => url('/admin/staff'), 'Tạo mới' => ''],
            'branches'   => $branches,
            'steps'      => $steps,
        ]);
    }

    public function storeStaff(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/staff/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'phone'    => 'required|phone|unique:users,phone',
            'name'     => 'required|min:2',
            'password' => 'required|min:6',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/staff/create');
            return;
        }

        $id = Auth::getInstance()->register([
            'phone'     => $this->input('phone'),
            'name'      => $this->input('name'),
            'password'  => $this->input('password'),
            'role'      => 'staff',
            'branch_id' => $this->input('branch_id') ?: null,
            'email'     => $this->input('email', ''),
            'is_active' => 1,
        ]);

        // Save production assignments
        $assignedSteps = $_POST['steps'] ?? [];
        foreach ($assignedSteps as $stepName) {
            $this->db->insert('production_assignments', [
                'user_id'    => $id,
                'step_name'  => $stepName,
                'branch_id'  => $this->input('branch_id') ?: null,
                'is_primary' => 1,
            ]);
        }

        $this->redirect('/admin/staff', ['success' => 'Đã tạo nhân viên']);
    }

    public function showStaff(string $id): void
    {
        $user = $this->db->fetch("SELECT u.*, b.name AS branch_name FROM users u LEFT JOIN branches b ON b.id=u.branch_id WHERE u.id=? AND u.role='staff'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/staff'); return; }

        $assignments = $this->db->fetchAll("SELECT * FROM production_assignments WHERE user_id=?", [(int)$id]);

        $completedSteps = $this->db->count('production_steps', "assigned_to=? AND status='completed'", [(int)$id]);

        $this->view('admin.users.show_staff', [
            'pageTitle'     => $user->name,
            'breadcrumbs'   => ['Nhân viên' => url('/admin/staff'), $user->name => ''],
            'user'          => $user,
            'assignments'   => $assignments,
            'completedSteps'=> $completedSteps,
        ]);
    }

    public function editStaff(string $id): void
    {
        $user     = $this->db->fetch("SELECT * FROM users WHERE id=? AND role='staff'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/staff'); return; }

        $branches    = $this->db->fetchAll("SELECT * FROM branches WHERE is_active=1 ORDER BY name");
        $steps       = $this->appConfig['production_steps'];
        $assignments = $this->db->fetchAll("SELECT step_name FROM production_assignments WHERE user_id=?", [(int)$id]);
        $assignedStepNames = array_column((array)$assignments, 'step_name');

        $this->view('admin.users.edit_staff', [
            'pageTitle'          => "Sửa: {$user->name}",
            'breadcrumbs'        => ['Nhân viên' => url('/admin/staff'), $user->name => url("/admin/staff/{$id}"), 'Sửa' => ''],
            'user'               => $user,
            'branches'           => $branches,
            'steps'              => $steps,
            'assignedStepNames'  => $assignedStepNames,
        ]);
    }

    public function updateStaff(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/staff/{$id}/edit"); return; }

        $this->db->update('users', [
            'name'      => $this->input('name'),
            'email'     => $this->input('email', ''),
            'branch_id' => $this->input('branch_id') ?: null,
        ], 'id=?', [(int)$id]);

        if ($newPass = $this->input('new_password')) {
            Auth::getInstance()->updatePassword((int)$id, $newPass);
        }

        // Update production assignments
        $this->db->delete('production_assignments', 'user_id=?', [(int)$id]);
        foreach ($_POST['steps'] ?? [] as $stepName) {
            $this->db->insert('production_assignments', [
                'user_id'   => (int)$id,
                'step_name' => $stepName,
                'branch_id' => $this->input('branch_id') ?: null,
                'is_primary'=> 1,
            ]);
        }

        $this->redirect("/admin/staff/{$id}", ['success' => 'Đã cập nhật nhân viên']);
    }

    public function toggleStaff(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/staff'); return; }

        $user = $this->db->fetch("SELECT id, is_active FROM users WHERE id=? AND role='staff'", [(int)$id]);
        if (!$user) { $this->redirect('/admin/staff'); return; }

        $this->db->update('users', ['is_active' => $user->is_active ? 0 : 1], 'id=?', [(int)$id]);
        $this->redirect('/admin/staff', ['success' => $user->is_active ? 'Đã khoá tài khoản' : 'Đã mở khoá']);
    }
}
