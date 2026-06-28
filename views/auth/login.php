<?php $layout = null; // standalone page - no layout wrapper ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Đăng nhập | HoanKiem LAB</title>
  <meta name="description" content="Đăng nhập vào hệ thống quản lý HoanKiem LAB">
  <link rel="icon" href="<?= asset('images/favicon.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">

    <!-- Logo -->
    <div class="auth-logo">
      <div class="auth-logo-icon">🦷</div>
      <h1>HoanKiem LAB</h1>
      <p>Hệ thống quản lý sản xuất răng giả</p>
    </div>

    <!-- Flash error -->
    <?php $error = get_flash('error'); if ($error): ?>
      <div class="alert alert-danger" data-auto-dismiss="5000">
        <i class="fa-solid fa-exclamation-circle"></i>
        <div><?= e($error) ?></div>
      </div>
    <?php endif; ?>
    <?php $success = get_flash('success'); if ($success): ?>
      <div class="alert alert-success" data-auto-dismiss="4000">
        <i class="fa-solid fa-check-circle"></i>
        <div><?= e($success) ?></div>
      </div>
    <?php endif; ?>

    <!-- Login Tabs -->
    <div class="auth-tabs" data-tabs>
      <div class="auth-tab active" data-tab-target="password">Mật khẩu</div>
      <div class="auth-tab" data-tab-target="code">Mã kích hoạt</div>
    </div>

    <!-- Password Login Form -->
    <div data-tab-panel="password">
      <form action="<?= url('/login') ?>" method="POST" id="login-password-form">
        <?= csrf_field() ?>
        <input type="hidden" name="activation_code" value="">

        <div class="form-group">
          <label class="form-label" for="phone">
            <i class="fa-solid fa-phone" style="margin-right:6px;color:var(--primary-light)"></i>
            Số điện thoại
          </label>
          <div class="input-group">
            <span class="input-addon">+84</span>
            <input
              type="tel"
              id="phone"
              name="phone"
              class="form-control <?= has_error('phone') ? 'is-invalid' : '' ?>"
              placeholder="09xxxxxxxx"
              value="<?= old('phone') ?>"
              required
              autocomplete="tel"
            >
          </div>
          <?= error('phone') ?>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">
            <i class="fa-solid fa-lock" style="margin-right:6px;color:var(--primary-light)"></i>
            Mật khẩu
          </label>
          <div style="position:relative;">
            <input
              type="password"
              id="password"
              name="password"
              class="form-control <?= has_error('password') ? 'is-invalid' : '' ?>"
              placeholder="••••••••"
              required
              autocomplete="current-password"
            >
            <button type="button"
              onclick="togglePass()"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;"
              id="toggle-pass-btn"
            >
              <i class="fa-solid fa-eye" id="pass-eye"></i>
            </button>
          </div>
          <?= error('password') ?>
        </div>

        <div class="flex items-center justify-between mb-4">
          <label class="form-check">
            <input type="checkbox" name="remember" value="1" checked>
            <span class="form-check-label">Ghi nhớ đăng nhập</span>
          </label>
        </div>

        <?php clear_flash_errors(); ?>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fa-solid fa-right-to-bracket"></i>
          Đăng nhập
        </button>
      </form>
    </div>

    <!-- Activation Code Form -->
    <div data-tab-panel="code" style="display:none;">
      <form action="<?= url('/login') ?>" method="POST" id="login-code-form">
        <?= csrf_field() ?>
        <input type="hidden" name="password" value="">

        <div class="form-group">
          <label class="form-label" for="phone2">
            <i class="fa-solid fa-phone" style="margin-right:6px;color:var(--primary-light)"></i>
            Số điện thoại
          </label>
          <div class="input-group">
            <span class="input-addon">+84</span>
            <input
              type="tel"
              id="phone2"
              name="phone"
              class="form-control"
              placeholder="09xxxxxxxx"
              autocomplete="tel"
            >
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="activation_code">
            <i class="fa-solid fa-key" style="margin-right:6px;color:var(--primary-light)"></i>
            Mã kích hoạt
          </label>
          <input
            type="text"
            id="activation_code"
            name="activation_code"
            class="form-control"
            placeholder="Nhập mã kích hoạt"
            autocomplete="off"
            style="letter-spacing:0.15em; font-weight:700; text-transform:uppercase;"
          >
          <div class="form-hint">Mã kích hoạt được cung cấp bởi quản trị viên</div>
        </div>

        <button type="submit" class="btn btn-primary btn-block btn-lg">
          <i class="fa-solid fa-key"></i>
          Xác nhận
        </button>
      </form>
    </div>

    <!-- Footer -->
    <div style="margin-top:24px; text-align:center; font-size:0.78rem; color:var(--text-muted);">
      <p>© <?= date('Y') ?> HoanKiem LAB · Hệ thống quản lý sản xuất răng giả</p>
    </div>
  </div>
</div>

<script>
  var baseUrl = '<?= url('') ?>';

  function togglePass() {
    const input = document.getElementById('password');
    const eye = document.getElementById('pass-eye');
    if (input.type === 'password') {
      input.type = 'text';
      eye.className = 'fa-solid fa-eye-slash';
    } else {
      input.type = 'password';
      eye.className = 'fa-solid fa-eye';
    }
  }
</script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
