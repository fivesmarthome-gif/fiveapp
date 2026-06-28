<div class="animate-fade-in">
  
  <!-- Profile Summary -->
  <div class="card mb-4">
    <div class="card-body text-center">
      <div style="position: relative; display: inline-block; margin-bottom: 12px;">
        <img src="<?= avatar_url($user->avatar) ?>" alt="Avatar" id="profile-avatar-preview" class="rounded-full border" style="width: 80px; height: 80px; object-fit: cover;">
        <form action="<?= url('/customer/profile/avatar') ?>" method="POST" enctype="multipart/form-data" id="avatar-form" style="position: absolute; right: 0; bottom: 0;">
          <?= csrf_field() ?>
          <label for="avatar-input" class="btn-icon rounded-full" style="width: 28px; height: 28px; background: var(--primary); border: none; color:#fff; cursor:pointer;">
            <i class="fa-solid fa-camera" style="font-size:0.75rem;"></i>
          </label>
          <input type="file" name="avatar" id="avatar-input" accept="image/*" style="display:none;" onchange="document.getElementById('avatar-form').submit();">
        </form>
      </div>
      <h3 class="font-bold text-lg"><?= e($user->name) ?></h3>
      <p class="text-xs text-muted"><?= e($user->phone) ?></p>
    </div>
  </div>

  <!-- General Info Tab -->
  <div class="card mb-4">
    <div class="card-header">
      <span class="card-title">Thông tin phòng khám</span>
    </div>
    <div class="card-body">
      <form action="<?= url('/customer/profile') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label class="form-label">Họ và tên bác sĩ</label>
          <input type="text" name="name" class="form-control" value="<?= e($user->name) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Tên phòng khám / Nha khoa</label>
          <input type="text" name="clinic_name" class="form-control" value="<?= e($user->clinic_name) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Bác sĩ đại diện</label>
          <input type="text" name="dentist_name" class="form-control" value="<?= e($user->dentist_name) ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Địa chỉ nhận trả hàng</label>
          <textarea name="address" class="form-control"><?= e($user->address) ?></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">Email liên hệ</label>
          <input type="email" name="email" class="form-control" value="<?= e($user->email) ?>">
        </div>

        <div class="form-group">
          <label class="form-check">
            <input type="checkbox" name="notify_promotion" value="1" <?= $user->notify_promotion ? 'checked' : '' ?>>
            <span class="form-check-label">Nhận thông báo chương trình khuyến mãi</span>
          </label>
        </div>

        <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-save"></i> Cập nhật thông tin</button>
      </form>
    </div>
  </div>

  <!-- Change Password -->
  <div class="card mb-4">
    <div class="card-header">
      <span class="card-title">Đổi mật khẩu</span>
    </div>
    <div class="card-body">
      <form action="<?= url('/customer/profile/password') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label class="form-label">Mật khẩu hiện tại</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Mật khẩu mới</label>
          <input type="password" name="new_password" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Xác nhận mật khẩu mới</label>
          <input type="password" name="new_password_confirmation" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-outline btn-block"><i class="fa-solid fa-key"></i> Thay đổi mật khẩu</button>
      </form>
    </div>
  </div>

  <!-- Logout -->
  <form action="<?= url('/logout') ?>" method="POST" class="mb-4">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-right-from-bracket"></i> Đăng xuất tài khoản</button>
  </form>

</div>
