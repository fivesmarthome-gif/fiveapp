<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa loại sản phẩm</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/product-types/{$productType->id}/edit") ?>" method="POST" id="form-product-type">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="name">Tên loại sản phẩm<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name', $productType->name) ?>" required>
        <?= error('name') ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="category">Phân loại chung</label>
        <select name="category" id="category" class="form-control">
          <option value="Răng Sứ" <?= old('category', $productType->category) == 'Răng Sứ' ? 'selected' : '' ?>>Răng Sứ (Cố định)</option>
          <option value="Tháo Lắp" <?= old('category', $productType->category) == 'Tháo Lắp' ? 'selected' : '' ?>>Hàm Tháo Lắp</option>
          <option value="Khác" <?= old('category', $productType->category) == 'Khác' ? 'selected' : '' ?>>Loại khác</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="description">Mô tả thêm (Tùy chọn)</label>
        <textarea name="description" id="description" class="form-control" rows="3"><?= old('description', $productType->description) ?></textarea>
      </div>

      <div class="flex" style="gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label class="form-label" for="base_price">Giá cơ bản (VNĐ)<span class="required">*</span></label>
          <input type="number" name="base_price" id="base_price" class="form-control <?= has_error('base_price') ? 'is-invalid' : '' ?>" value="<?= old('base_price', $productType->base_price) ?>" required min="0" step="1000">
          <?= error('base_price') ?>
        </div>

        <div class="form-group" style="flex: 1;">
          <label class="form-label" for="estimated_days">Thời gian hoàn thành (Ngày)</label>
          <input type="number" name="estimated_days" id="estimated_days" class="form-control" value="<?= old('estimated_days', $productType->estimated_days) ?>" min="1">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label" for="sort_order">Thứ tự hiển thị (Nhỏ xếp trước)</label>
        <input type="number" name="sort_order" id="sort_order" class="form-control" value="<?= old('sort_order', $productType->sort_order) ?>">
      </div>

      <div class="form-group">
        <label class="form-label">
          <input type="checkbox" name="is_active" value="1" <?= old('is_active', $productType->is_active) ? 'checked' : '' ?>>
          Kích hoạt hoạt động
        </label>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/product-types') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật sản phẩm</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('form-product-type').addEventListener('submit', function(e) {
    if (!validateForm('form-product-type')) {
      e.preventDefault();
    }
  });
</script>
