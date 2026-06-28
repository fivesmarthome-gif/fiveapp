<?php
/**
 * Admin Promotion Controller
 * HoanKiem LAB
 */

class PromotionController extends Controller
{
    public function index(): void
    {
        $promotions = $this->db->fetchAll("SELECT * FROM promotions ORDER BY end_date DESC");
        $this->view('admin.promotions.index', [
            'pageTitle' => 'Quản lý chương trình khuyến mãi',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Khuyến mãi' => ''],
            'promotions' => $promotions
        ]);
    }

    public function create(): void
    {
        $this->view('admin.promotions.create', [
            'pageTitle' => 'Tạo chương trình khuyến mãi mới',
            'breadcrumbs' => ['Khuyến mãi' => url('/admin/promotions'), 'Tạo mới' => '']
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/promotions/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'title' => 'required',
            'code' => 'required|unique:promotions,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/promotions/create');
            return;
        }

        $coverImagePath = $this->uploadFile('cover_image', 'promotions');

        $id = $this->db->insert('promotions', [
            'title' => $this->input('title'),
            'code' => strtoupper($this->input('code')),
            'type' => $this->input('type'),
            'value' => (float)$this->input('value'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'description' => $this->input('description', ''),
            'cover_image' => $coverImagePath,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->logActivity('create_promotion', 'promotions', $id, "Tạo khuyến mãi: {$this->input('title')}");
        $this->redirect('/admin/promotions', ['success' => 'Tạo khuyến mãi thành công']);
    }

    public function edit(string $id): void
    {
        $promotion = $this->db->fetch("SELECT * FROM promotions WHERE id = ?", [(int)$id]);
        if (!$promotion) { $this->redirect('/admin/promotions'); return; }

        $this->view('admin.promotions.edit', [
            'pageTitle' => 'Chỉnh sửa chương trình khuyến mãi',
            'breadcrumbs' => ['Khuyến mãi' => url('/admin/promotions'), 'Sửa' => ''],
            'promotion' => $promotion
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/promotions/{$id}/edit"); return; }

        $promotion = $this->db->fetch("SELECT * FROM promotions WHERE id = ?", [(int)$id]);
        if (!$promotion) { $this->redirect('/admin/promotions'); return; }

        $coverImagePath = $this->uploadFile('cover_image', 'promotions') ?: $promotion->cover_image;

        $this->db->update('promotions', [
            'title' => $this->input('title'),
            'type' => $this->input('type'),
            'value' => (float)$this->input('value'),
            'start_date' => $this->input('start_date'),
            'end_date' => $this->input('end_date'),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'description' => $this->input('description', ''),
            'cover_image' => $coverImagePath,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [$promotion->id]);

        $this->redirect('/admin/promotions', ['success' => 'Đập nhật khuyến mãi thành công']);
    }

    public function destroy(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/promotions'); return; }

        $this->db->delete('promotions', 'id = ?', [(int)$id]);
        $this->redirect('/admin/promotions', ['success' => 'Đã xoá chương trình khuyến mãi']);
    }
}
