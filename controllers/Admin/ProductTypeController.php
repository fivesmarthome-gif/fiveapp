<?php
/**
 * Admin ProductType Controller
 * HoanKiem LAB
 */

class ProductTypeController extends Controller
{
    public function index(): void
    {
        $productTypes = $this->db->fetchAll("SELECT * FROM product_types ORDER BY sort_order ASC, name ASC");
        $this->view('admin.product_types.index', [
            'pageTitle' => 'Loại sản phẩm',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Sản phẩm' => ''],
            'productTypes' => $productTypes
        ]);
    }

    public function create(): void
    {
        $this->view('admin.product_types.create', [
            'pageTitle' => 'Thêm loại sản phẩm mới',
            'breadcrumbs' => ['Sản phẩm' => url('/admin/product-types'), 'Thêm mới' => '']
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/product-types/create', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'name' => 'required',
            'base_price' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/product-types/create');
            return;
        }

        $id = $this->db->insert('product_types', [
            'name' => $this->input('name'),
            'category' => $this->input('category', ''),
            'description' => $this->input('description', ''),
            'estimated_days' => (int)$this->input('estimated_days', 5),
            'base_price' => (float)$this->input('base_price'),
            'sort_order' => (int)$this->input('sort_order', 0),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->redirect('/admin/product-types', ['success' => 'Đã thêm loại sản phẩm thành công']);
    }

    public function edit(string $id): void
    {
        $productType = $this->db->fetch("SELECT * FROM product_types WHERE id = ?", [(int)$id]);
        if (!$productType) { $this->redirect('/admin/product-types'); return; }

        $this->view('admin.product_types.edit', [
            'pageTitle' => 'Chỉnh sửa loại sản phẩm',
            'breadcrumbs' => ['Sản phẩm' => url('/admin/product-types'), 'Sửa' => ''],
            'productType' => $productType
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/product-types/{$id}/edit"); return; }

        $this->db->update('product_types', [
            'name' => $this->input('name'),
            'category' => $this->input('category', ''),
            'description' => $this->input('description', ''),
            'estimated_days' => (int)$this->input('estimated_days', 5),
            'base_price' => (float)$this->input('base_price'),
            'sort_order' => (int)$this->input('sort_order', 0),
            'is_active' => $this->input('is_active', 0) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ], 'id = ?', [(int)$id]);

        $this->redirect('/admin/product-types', ['success' => 'Cập nhật loại sản phẩm thành công']);
    }
}
