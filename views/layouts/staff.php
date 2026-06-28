<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= e($pageTitle ?? 'HoanKiem LAB') ?> | Nhân viên</title>
  <meta name="theme-color" content="#6366f1">
  <link rel="icon" href="<?= asset('images/favicon.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <?= $extraHead ?? '' ?>
</head>
<body>
<div class="customer-layout">

  <!-- Top Bar -->
  <header class="customer-topbar">
    <?php if (!empty($backUrl)): ?>
    <a href="<?= $backUrl ?>" class="btn-icon">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <?php else: ?>
    <div class="logo-icon" style="width:32px;height:32px;font-size:16px;">👷</div>
    <?php endif; ?>

    <h1><?= e($pageTitle ?? 'Nhân viên') ?></h1>

    <a href="<?= url('/staff/notifications') ?>" class="topbar-btn" style="margin-left:auto;">
      <i class="fa-solid fa-bell"></i>
      <?php if ($unreadNotifications > 0): ?>
        <span class="topbar-badge"></span>
      <?php endif; ?>
    </a>
  </header>

  <!-- Flash Messages -->
  <?php $success = get_flash('success'); if ($success): ?>
    <div class="alert alert-success" data-auto-dismiss="3000" style="margin:12px 16px 0; border-radius:10px;">
      <i class="fa-solid fa-check-circle"></i>
      <div><?= e($success) ?></div>
    </div>
  <?php endif; ?>
  <?php $error = get_flash('error'); if ($error): ?>
    <div class="alert alert-danger" data-auto-dismiss="4000" style="margin:12px 16px 0; border-radius:10px;">
      <i class="fa-solid fa-exclamation-circle"></i>
      <div><?= e($error) ?></div>
    </div>
  <?php endif; ?>

  <!-- Page Content -->
  <main class="animate-fade-in" style="padding: 12px 16px 16px;">
    <?= $content ?? '' ?>
  </main>

  <!-- Bottom Navigation -->
  <nav class="bottom-nav">
    <a href="<?= url('/staff/dashboard') ?>" class="bottom-nav-item <?= is_active('/staff/dashboard') ? 'active' : '' ?>">
      <i class="fa-solid fa-house"></i>
      <span>Trang chủ</span>
    </a>
    <a href="<?= url('/staff/production') ?>" class="bottom-nav-item <?= is_active('/staff/production') ? 'active' : '' ?>">
      <i class="fa-solid fa-gears"></i>
      <span>Công việc</span>
    </a>
    <a href="<?= url('/staff/appointments') ?>" class="bottom-nav-item <?= is_active('/staff/appointments') ? 'active' : '' ?>">
      <i class="fa-solid fa-calendar-check"></i>
      <span>Lịch hẹn</span>
    </a>
    <a href="<?= url('/staff/profile') ?>" class="bottom-nav-item <?= is_active('/staff/profile') ? 'active' : '' ?>">
      <i class="fa-solid fa-user"></i>
      <span>Tôi</span>
    </a>
  </nav>

</div>

<script>
  var baseUrl = '<?= url('') ?>';
</script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
