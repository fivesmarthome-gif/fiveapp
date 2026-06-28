<div class="section-header animate-fade-in">
  <h2 class="section-title">Hồ sơ cá nhân</h2>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 20px;">
  <div>
    <div class="card animate-fade-in">
      <div class="card-body" style="text-align: center; padding: 40px 20px;">
        <div style="width: 100px; height: 100px; border-radius: 50%; background: linear-gradient(135deg, #7367f0 0%, #a59ef5 100%); display: flex; align-items: center; justify-content: center; font-size: 3rem; color: white; margin: 0 auto 20px; font-weight: bold;">
          <?= strtoupper(substr($user->name, 0, 1)) ?>
        </div>
        <div style="font-size: 1.2rem; font-weight: 700; color: white;"><?= e($user->name) ?></div>
        <div style="color: #a3a4cc; margin-bottom: 15px;"><?= e($user->email ?: $user->phone) ?></div>
        <span class="badge badge-primary"><?= ucfirst($user->role) ?></span>
      </div>
    </div>

    <div class="card mt-4 animate-fade-in animate-delay-1">
      <div class="card-header">
        <span class="card-title">Đổi mật khẩu</span>
      </div>
      <div class="card-body">
        <form action="<?= url('/admin/profile/password') ?>" method="POST" id="form-password">
          <?= csrf_field() ?>
          
          <div class="form-group">
            <label class="form-label" for="current_password">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" id="current_password" class="form-control" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="new_password">Mật khẩu mới</label>
            <input type="password" name="new_password" id="new_password" class="form-control" required minlength="6">
          </div>

          <div class="form-group">
            <label class="form-label" for="new_password_confirmation">Xác nhận mật khẩu mới</label>
            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required minlength="6">
          </div>

          <button type="submit" class="btn btn-primary w-full mt-2"><i class="fa-solid fa-key"></i> Đổi mật khẩu</button>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card animate-fade-in">
      <div class="card-header">
        <span class="card-title">Cập nhật thông tin</span>
      </div>
      <div class="card-body">
        <form action="<?= url('/admin/profile') ?>" method="POST" id="form-profile">
          <?= csrf_field() ?>

          <div class="form-group">
            <label class="form-label" for="phone">Số điện thoại (Tên đăng nhập)</label>
            <input type="text" id="phone" class="form-control" value="<?= e($user->phone) ?>" disabled style="opacity: 0.7; cursor: not-allowed;">
            <div class="text-xs text-muted mt-1">Không thể thay đổi số điện thoại dùng để đăng nhập.</div>
          </div>

          <div class="form-group">
            <label class="form-label" for="name">Họ và tên<span class="required">*</span></label>
            <input type="text" name="name" id="name" class="form-control" value="<?= e($user->name) ?>" required>
          </div>

          <div class="form-group">
            <label class="form-label" for="email">Địa chỉ Email</label>
            <input type="email" name="email" id="email" class="form-control" value="<?= e($user->email) ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="gender">Giới tính</label>
            <select name="gender" id="gender" class="form-control">
              <option value="male" <?= $user->gender == 'male' ? 'selected' : '' ?>>Nam</option>
              <option value="female" <?= $user->gender == 'female' ? 'selected' : '' ?>>Nữ</option>
              <option value="other" <?= $user->gender == 'other' ? 'selected' : '' ?>>Khác</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="birthday">Ngày sinh</label>
            <input type="date" name="birthday" id="birthday" class="form-control" value="<?= e($user->birthday) ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="address">Địa chỉ liên hệ</label>
            <textarea name="address" id="address" class="form-control" rows="3"><?= e($user->address) ?></textarea>
          </div>

          <div class="mt-6 flex justify-end">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật hồ sơ</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('form-password').addEventListener('submit', function(e) {
    if (document.getElementById('new_password').value !== document.getElementById('new_password_confirmation').value) {
      e.preventDefault();
      alert('Mật khẩu mới và mật khẩu xác nhận không khớp!');
    }
  });
</script>
