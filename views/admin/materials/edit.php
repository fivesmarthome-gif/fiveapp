<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa thông tin vật liệu</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/materials/{$material->id}/edit") ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Mã vật liệu (Không thể sửa)</label>
        <input type="text" class="form-control" value="<?= e($material->code) ?>" disabled style="opacity: 0.6;">
      </div>

      <div class="form-group">
        <label class="form-label" for="name">Tên vật liệu<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control" value="<?= e($material->name) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="category">Danh mục phân loại<span class="required">*</span></label>
        <select name="category" id="category" class="form-control" required>
          <option value="ceramic" <?= $material->category === 'ceramic' ? 'selected' : '' ?>>Sứ / Zirconia</option>
          <option value="metal" <?= $material->category === 'metal' ? 'selected' : '' ?>>Kim loại / Khung sườn</option>
          <option value="acrylic" <?= $material->category === 'acrylic' ? 'selected' : '' ?>>Nhựa / Sáp thiết kế</option>
          <option value="consumable" <?= $material->category === 'consumable' ? 'selected' : '' ?>>Vật liệu tiêu hao khác</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="unit">Đơn vị tính<span class="required">*</span></label>
        <select name="unit" id="unit" class="form-control" required>
          <option value="piece" <?= $material->unit === 'piece' ? 'selected' : '' ?>>Cái / Chiếc / Bánh</option>
          <option value="gram" <?= $material->unit === 'gram' ? 'selected' : '' ?>>Gram</option>
          <option value="ml" <?= $material->unit === 'ml' ? 'selected' : '' ?>>Mililít (ml)</option>
        </select>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label" for="current_stock">Số lượng tồn kho</label>
          <input type="number" name="current_stock" id="current_stock" class="form-control" value="<?= e($material->current_stock) ?>" step="any" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="min_stock">Ngưỡng cảnh báo hết hàng</label>
          <input type="number" name="min_stock" id="min_stock" class="form-control" value="<?= e($material->min_stock) ?>" step="any">
        </div>
      </div>

      <div class="grid grid-2">
        <div class="form-group">
          <label class="form-label" for="unit_cost">Đơn giá vốn ước tính (đ)</label>
          <input type="number" name="unit_cost" id="unit_cost" class="form-control" value="<?= e($material->unit_cost) ?>">
        </div>

        <div class="form-group">
          <label class="form-label" for="supplier_id">Nhà cung cấp chính</label>
          <select name="supplier_id" id="supplier_id" class="form-control">
            <option value="">-- Chọn nhà cung cấp --</option>
            <?php foreach ($suppliers as $s): ?>
              <option value="<?= $s->id ?>" <?= $s->id == $material->supplier_id ? 'selected' : '' ?>><?= e($s->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="expiry_date">Hạn sử dụng</label>
        <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="<?= e($material->expiry_date) ?>">
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/materials') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật vật liệu</button>
      </div>
    </form>
  </div>
</div>
