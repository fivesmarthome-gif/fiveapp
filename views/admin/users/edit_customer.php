<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa thông tin khách hàng</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/customers/{$user->id}/edit") ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Số điện thoại đăng nhập (Không thể sửa)</label>
        <input type="text" class="form-control" value="<?= e($user->phone) ?>" disabled style="opacity: 0.6;">
      </div>

      <div class="form-group">
        <label class="form-label" for="new_password">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Nhập mật khẩu mới ít nhất 6 ký tự">
      </div>

      <div class="form-group">
        <label class="form-label" for="name">Họ và tên bác sĩ đại diện<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control" value="<?= e($user->name) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="clinic_name">Tên phòng khám / Nha khoa</label>
        <input type="text" name="clinic_name" id="clinic_name" class="form-control" value="<?= e($user->clinic_name) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="dentist_name">Bác sĩ phụ trách chính</label>
        <input type="text" name="dentist_name" id="dentist_name" class="form-control" value="<?= e($user->dentist_name) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="address">Địa chỉ nhận trả hàng</label>
        <textarea name="address" id="address" class="form-control"><?= e($user->address) ?></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Địa chỉ Email</label>
        <input type="email" name="email" id="email" class="form-control" value="<?= e($user->email) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="tax_code">Mã số thuế</label>
        <input type="text" name="tax_code" id="tax_code" class="form-control" value="<?= e($user->tax_code) ?>">
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url("/admin/customers/{$user->id}") ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật thông tin</button>
      </div>
    </form>
  </div>
</div>
