<div class="card animate-fade-in" style="max-width: 800px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Thêm khuyến mãi mới</span>
  </div>
  <div class="card-body">
    <form action="<?= url('/admin/promotions/create') ?>" method="POST" enctype="multipart/form-data" id="form-promo">
      <?= csrf_field() ?>

      <div class="flex" style="gap: 20px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
          <div class="form-group">
            <label class="form-label" for="name">Tên chương trình<span class="required">*</span></label>
            <input type="text" name="name" id="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name') ?>" required>
            <?= error('name') ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="description">Mô tả</label>
            <textarea name="description" id="description" class="form-control" rows="4"><?= old('description') ?></textarea>
          </div>

          <div class="flex" style="gap: 16px;">
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="discount_type">Loại chiết khấu<span class="required">*</span></label>
              <select name="discount_type" id="discount_type" class="form-control <?= has_error('discount_type') ? 'is-invalid' : '' ?>" required>
                <option value="percent" <?= old('discount_type') == 'percent' ? 'selected' : '' ?>>Phần trăm (%)</option>
                <option value="fixed" <?= old('discount_type') == 'fixed' ? 'selected' : '' ?>>Số tiền cố định (VNĐ)</option>
              </select>
              <?= error('discount_type') ?>
            </div>
            
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="discount_value">Mức chiết khấu<span class="required">*</span></label>
              <input type="number" name="discount_value" id="discount_value" class="form-control <?= has_error('discount_value') ? 'is-invalid' : '' ?>" value="<?= old('discount_value') ?>" required min="0">
              <?= error('discount_value') ?>
            </div>
          </div>
        </div>

        <div style="flex: 1; min-width: 300px;">
          <div class="form-group">
            <label class="form-label" for="code">Mã khuyến mãi (Code)<span class="required">*</span></label>
            <input type="text" name="code" id="code" class="form-control <?= has_error('code') ? 'is-invalid' : '' ?>" value="<?= old('code') ?>" required style="text-transform: uppercase;">
            <div class="text-xs text-muted mt-1">Mã dùng để nhập khi thanh toán. Ví dụ: SUMMER2024</div>
            <?= error('code') ?>
          </div>

          <div class="flex" style="gap: 16px;">
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="start_date">Ngày bắt đầu<span class="required">*</span></label>
              <input type="datetime-local" name="start_date" id="start_date" class="form-control <?= has_error('start_date') ? 'is-invalid' : '' ?>" value="<?= old('start_date') ?>" required>
              <?= error('start_date') ?>
            </div>
            
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="end_date">Ngày kết thúc<span class="required">*</span></label>
              <input type="datetime-local" name="end_date" id="end_date" class="form-control <?= has_error('end_date') ? 'is-invalid' : '' ?>" value="<?= old('end_date') ?>" required>
              <?= error('end_date') ?>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="image">Banner / Hình ảnh</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(this, 'image-preview')">
            <div class="mt-2" id="image-preview" style="display: none; width: 100%; height: 120px; border-radius: 6px; background-size: cover; background-position: center; border: 1px dashed #2f3349;"></div>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/promotions') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Tạo khuyến mãi</button>
      </div>
    </form>
  </div>
</div>

<script>
  function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.style.display = 'block';
        preview.style.backgroundImage = 'url(' + e.target.result + ')';
      }
      reader.readAsDataURL(input.files[0]);
    } else {
      preview.style.display = 'none';
      preview.style.backgroundImage = 'none';
    }
  }

  document.getElementById('form-promo').addEventListener('submit', function(e) {
    if (!validateForm('form-promo')) {
      e.preventDefault();
    }
  });
</script>
