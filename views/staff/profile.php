<div class="animate-fade-in">
  
  <!-- Profile Summary -->
  <div class="card mb-4">
    <div class="card-body text-center">
      <img src="<?= avatar_url($user->avatar) ?>" alt="Avatar" class="rounded-full border mb-3" style="width: 80px; height: 80px; object-fit: cover; display:inline-block;">
      <h3 class="font-bold text-lg"><?= e($user->name) ?></h3>
      <p class="text-xs text-muted"><?= e($user->phone) ?> (Nhân viên kỹ thuật)</p>
    </div>
  </div>

  <!-- General Info Tab -->
  <div class="card mb-4">
    <div class="card-header">
      <span class="card-title">Thông tin cá nhân</span>
    </div>
    <div class="card-body">
      <form action="<?= url('/staff/profile') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label class="form-label">Họ và tên</label>
          <input type="text" name="name" class="form-control" value="<?= e($user->name) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label">Email liên hệ</label>
          <input type="email" name="email" class="form-control" value="<?= e($user->email) ?>">
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
      <form action="<?= url('/staff/profile/password') ?>" method="POST">
        <?= csrf_field() ?>
        
        <div class="form-group">
          <label class="form-label">Mật khẩu hiện tại</label>
          <input type="password" name="current_password" class="form-control" required>
        </div>

        <div class="form-group">
          <label class="form-label">Mật khẩu mới</label>
          <input type="password" name="new_password" class="form-control" required>
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
