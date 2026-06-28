<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Điền thông tin khách hàng mới</span>
  </div>
  <div class="card-body">
    <form action="<?= url('/admin/customers/create') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="phone">Số điện thoại đăng nhập SĐT<span class="required">*</span></label>
        <input type="tel" name="phone" id="phone" class="form-control" placeholder="Ví dụ: 0912345678" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Mật khẩu đăng nhập<span class="required">*</span></label>
        <input type="password" name="password" id="password" class="form-control" placeholder="Nhập mật khẩu ít nhất 6 ký tự" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="name">Họ và tên bác sĩ đại diện<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control" placeholder="Bác sĩ Nguyễn Văn A" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="clinic_name">Tên phòng khám / Nha khoa</label>
        <input type="text" name="clinic_name" id="clinic_name" class="form-control" placeholder="Nha khoa Hoàn Kiếm">
      </div>

      <div class="form-group">
        <label class="form-label" for="dentist_name">Bác sĩ phụ trách chính</label>
        <input type="text" name="dentist_name" id="dentist_name" class="form-control" placeholder="Bác sĩ phụ trách">
      </div>

      <div class="form-group">
        <label class="form-label" for="address">Địa chỉ nhận trả hàng</label>
        <textarea name="address" id="address" class="form-control" placeholder="Nhập số nhà, tên đường, quận huyện..."></textarea>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Địa chỉ Email</label>
        <input type="email" name="email" id="email" class="form-control" placeholder="bacsi@example.com">
      </div>

      <div class="form-group">
        <label class="form-label" for="tax_code">Mã số thuế phòng khám</label>
        <input type="text" name="tax_code" id="tax_code" class="form-control" placeholder="Nhập mã số thuế nếu cần xuất hóa đơn">
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/customers') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Tạo tài khoản</button>
      </div>
    </form>
  </div>
</div>
