<?php
/**
 * Admin Material Controller
 * HoanKiem LAB
 */

class MaterialController extends Controller
{
    public function index(): void
    {
        $search = trim($this->input('search', ''));
        $where = "1=1";
        $params = [];
        if ($search) {
            $where .= " AND (name LIKE ? OR code LIKE ?)";
            $params = ["%$search%", "%$search%"];
        }

        $materials = $this->db->fetchAll("SELECT m.*, s.name AS supplier_name FROM materials m LEFT JOIN suppliers s ON s.id = m.supplier_id WHERE {$where} ORDER BY m.name ASC", $params);
        $suppliers = $this->db->fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

        $this->view('admin.materials.index', [
            'pageTitle' => 'Quản lý vật liệu & kho',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Vật liệu' => ''],
            'materials' => $materials,
            'suppliers' => $suppliers,
            'search' => $search
        ]);
    }

    public function create(): void
    {
        $suppliers = $this->db->fetchAll("SELECT * FROM suppliers ORDER BY name ASC");
        $this->view('admin.materials.create', [
            'pageTitle' => 'Thêm vật liệu mới',
            'breadcrumbs' => ['Vật liệu' => url('/admin/materials'), 'Thêm mới' => ''],
            'suppliers' => $suppliers
        ]);
    }

    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/materials', ['error' => 'CSRF error']); return; }

        $validation = $this->validate([
            'code' => 'required|unique:materials,code',
            'name' => 'required',
            'category' => 'required',
            'unit' => 'required',
            'current_stock' => 'required|numeric',
        ]);

        if (!$validation['valid']) {
            $this->redirect('/admin/materials/create');
            return;
        }

        $id = $this->db->insert('materials', [
            'code' => $this->input('code'),
            'name' => $this->input('name'),
            'category' => $this->input('category'),
            'unit' => $this->input('unit'),
            'current_stock' => (float)$this->input('current_stock'),
            'min_stock' => (float)$this->input('min_stock', 0),
            'unit_cost' => (float)$this->input('unit_cost', 0),
            'supplier_id' => $this->input('supplier_id') ?: null,
            'expiry_date' => $this->input('expiry_date') ?: null,
        ]);

        $this->logActivity('create_material', 'materials', $id, "Thêm vật liệu {$this->input('name')}");
        $this->redirect('/admin/materials', ['success' => 'Đã thêm vật liệu mới']);
    }

    public function edit(string $id): void
    {
        $material = $this->db->fetch("SELECT * FROM materials WHERE id = ?", [(int)$id]);
        if (!$material) { $this->redirect('/admin/materials'); return; }

        $suppliers = $this->db->fetchAll("SELECT * FROM suppliers ORDER BY name ASC");

        $this->view('admin.materials.edit', [
            'pageTitle' => "Sửa: {$material->name}",
            'breadcrumbs' => ['Vật liệu' => url('/admin/materials'), 'Sửa' => ''],
            'material' => $material,
            'suppliers' => $suppliers
        ]);
    }

    public function update(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/materials/{$id}/edit"); return; }

        $this->db->update('materials', [
            'name' => $this->input('name'),
            'category' => $this->input('category'),
            'unit' => $this->input('unit'),
            'current_stock' => (float)$this->input('current_stock'),
            'min_stock' => (float)$this->input('min_stock', 0),
            'unit_cost' => (float)$this->input('unit_cost', 0),
            'supplier_id' => $this->input('supplier_id') ?: null,
            'expiry_date' => $this->input('expiry_date') ?: null,
        ], 'id = ?', [(int)$id]);

        $this->redirect('/admin/materials', ['success' => 'Đã cập nhật thông tin vật liệu']);
    }

    public function transaction(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/materials'); return; }

        $material = $this->db->fetch("SELECT * FROM materials WHERE id = ?", [(int)$id]);
        if (!$material) { $this->redirect('/admin/materials'); return; }

        $type = $this->input('type'); // import or export
        $qty = (float)$this->input('quantity');
        $notes = $this->input('notes');

        if ($qty <= 0) {
            $this->redirect('/admin/materials', ['error' => 'Số lượng phải lớn hơn 0']);
            return;
        }

        $newStock = $material->current_stock;
        if ($type === 'import') {
            $newStock += $qty;
        } else {
            $newStock -= $qty;
            if ($newStock < 0) {
                $this->redirect('/admin/materials', ['error' => 'Số lượng tồn kho không đủ để xuất']);
                return;
            }
        }

        $this->db->beginTransaction();
        try {
            $this->db->update('materials', ['current_stock' => $newStock], 'id = ?', [$material->id]);

            $this->db->insert('material_transactions', [
                'material_id' => $material->id,
                'type' => $type,
                'quantity' => $qty,
                'notes' => $notes,
                'performed_by' => Auth::getInstance()->id(),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->db->commit();
            $this->redirect('/admin/materials', ['success' => 'Đã cập nhật kho thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect('/admin/materials', ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
