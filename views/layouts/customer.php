<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= e($pageTitle ?? 'HoanKiem LAB') ?> | Khách hàng</title>
  <meta name="description" content="HoanKiem LAB - Theo dõi đơn hàng răng giả">
  <meta name="theme-color" content="#6366f1">
  <link rel="manifest" href="<?= url('/public/manifest.json') ?>">
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
    <a href="<?= $backUrl ?>" class="btn-icon" title="Quay lại">
      <i class="fa-solid fa-arrow-left"></i>
    </a>
    <?php else: ?>
    <div class="logo-icon" style="width:32px;height:32px;font-size:16px;">🦷</div>
    <?php endif; ?>

    <h1><?= e($pageTitle ?? 'HoanKiem LAB') ?></h1>

    <!-- Notifications -->
    <a href="<?= url('/customer/notifications') ?>" class="topbar-btn" title="Thông báo" style="margin-left:auto;">
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

  <!-- Floating Hotline -->
  <?php $hotline = setting('hotline', '1900 xxxx'); ?>
  <a href="tel:<?= preg_replace('/\s+/', '', $hotline) ?>" class="fab-hotline" title="Gọi tổng đài">
    <i class="fa-solid fa-phone"></i>
  </a>

  <!-- Bottom Navigation -->
  <nav class="bottom-nav">
    <a href="<?= url('/customer/dashboard') ?>" class="bottom-nav-item <?= is_active('/customer/dashboard') ? 'active' : '' ?>">
      <i class="fa-solid fa-house"></i>
      <span>Trang chủ</span>
    </a>
    <a href="<?= url('/customer/orders') ?>" class="bottom-nav-item <?= is_active('/customer/orders') ? 'active' : '' ?>">
      <i class="fa-solid fa-clipboard-list"></i>
      <span>Đơn hàng</span>
    </a>
    <a href="<?= url('/customer/notifications') ?>" class="bottom-nav-item <?= is_active('/customer/notifications') ? 'active' : '' ?>">
      <i class="fa-solid fa-bell"></i>
      <span>Thông báo</span>
      <?php if ($unreadNotifications > 0): ?>
        <span class="nav-dot"></span>
      <?php endif; ?>
    </a>
    <a href="<?= url('/customer/profile') ?>" class="bottom-nav-item <?= is_active('/customer/profile') ? 'active' : '' ?>">
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
