<?php
/**
 * Admin Branch Controller
 * HoanKiem LAB
 */

class BranchController extends Controller
{
    public function index(): void
    {
        $branches = $this->db->fetchAll("SELECT * FROM branches ORDER BY name ASC");
        $this->view('admin.branches.index', [
            'pageTitle' => 'Quản lý chi nhánh',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Chi nhánh' => ''],
            'branches' => $branches
        ]);
    }

    public function create(): void
    {
        $this->view('admin.branches.create', [
            'pageTitle' => 'Thêm chi nhánh mới',
            'breadcrumbs' => ['Chi nhánh' => url('/admin/branches'), 'Thêm mới' => '']
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/branches/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'name' => 'required',
            'phone' => 'required',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/branches/create');
            return;
        }

        $id = $this->db->insert('branches', [
            'name' => $this->input('name'),
            'address' => $this->input('address', ''),
            'phone' => $this->input('phone'),
            'hotline' => $this->input('hotline', ''),
            'working_hours' => $this->input('working_hours', '08:00 - 17:30'),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->logActivity('create_branch', 'branches', $id, "Thêm chi nhánh {$this->input('name')}");
        $this->redirect('/admin/branches', ['success' => 'Thêm chi nhánh thành công']);
    }

    public function edit(string $id): void
    {
        $branch = $this->db->fetch("SELECT * FROM branches WHERE id = ?", [(int)$id]);
        if (!$branch) { $this->redirect('/admin/branches'); return; }

        $this->view('admin.branches.edit', [
            'pageTitle' => 'Chỉnh sửa chi nhánh',
            'breadcrumbs' => ['Chi nhánh' => url('/admin/branches'), 'Sửa' => ''],
            'branch' => $branch
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/branches/{$id}/edit"); return; }

        $this->db->update('branches', [
            'name' => $this->input('name'),
            'address' => $this->input('address', ''),
            'phone' => $this->input('phone'),
            'hotline' => $this->input('hotline', ''),
            'working_hours' => $this->input('working_hours', '08:00 - 17:30'),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [(int)$id]);

        $this->redirect('/admin/branches', ['success' => 'Cập nhật chi nhánh thành công']);
    }
}
