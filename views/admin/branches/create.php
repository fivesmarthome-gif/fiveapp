<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Thêm chi nhánh mới</span>
  </div>
  <div class="card-body">
    <form action="<?= url('/admin/branches/create') ?>" method="POST" id="form-branch">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="name">Tên chi nhánh<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control <?= has_error('name') ? 'is-invalid' : '' ?>" value="<?= old('name') ?>" required>
        <?= error('name') ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="address">Địa chỉ</label>
        <input type="text" name="address" id="address" class="form-control" value="<?= old('address') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="phone">Số điện thoại<span class="required">*</span></label>
        <input type="text" name="phone" id="phone" class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>" value="<?= old('phone') ?>" required>
        <?= error('phone') ?>
      </div>

      <div class="form-group">
        <label class="form-label" for="hotline">Hotline hỗ trợ</label>
        <input type="text" name="hotline" id="hotline" class="form-control" value="<?= old('hotline') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="working_hours">Giờ làm việc</label>
        <input type="text" name="working_hours" id="working_hours" class="form-control" value="<?= old('working_hours', '08:00 - 17:30') ?>">
      </div>

      <div class="form-group">
        <label class="form-label">
          <input type="checkbox" name="is_active" value="1" <?= old('is_active', 1) ? 'checked' : '' ?>>
          Kích hoạt hoạt động
        </label>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/branches') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Thêm chi nhánh</button>
      </div>
    </form>
  </div>
</div>

<script>
  document.getElementById('form-branch').addEventListener('submit', function(e) {
    if (!validateForm('form-branch')) {
      e.preventDefault();
    }
  });
</script>
