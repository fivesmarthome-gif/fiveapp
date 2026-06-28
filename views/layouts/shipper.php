<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
  <title><?= e($pageTitle ?? 'HoanKiem LAB') ?> | Shipper</title>
  <meta name="theme-color" content="#3b82f6">
  <link rel="icon" href="<?= asset('images/favicon.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <?= $extraHead ?? '' ?>
</head>
<body>
<div class="customer-layout">
  <header class="customer-topbar">
    <?php if (!empty($backUrl)): ?>
      <a href="<?= $backUrl ?>" class="btn-icon"><i class="fa-solid fa-arrow-left"></i></a>
    <?php else: ?>
      <div class="logo-icon" style="width:32px;height:32px;font-size:16px;"><i class="fa-solid fa-truck-fast"></i></div>
    <?php endif; ?>

    <h1><?= e($pageTitle ?? 'Shipper') ?></h1>

    <form action="<?= url('/logout') ?>" method="POST" style="margin-left:auto;">
      <?= csrf_field() ?>
      <button type="submit" class="topbar-btn" title="Đăng xuất"><i class="fa-solid fa-right-from-bracket"></i></button>
    </form>
  </header>

  <?php $success = get_flash('success'); if ($success): ?>
    <div class="alert alert-success" data-auto-dismiss="3000" style="margin:12px 16px 0; border-radius:10px;">
      <i class="fa-solid fa-check-circle"></i><div><?= e($success) ?></div>
    </div>
  <?php endif; ?>
  <?php $error = get_flash('error'); if ($error): ?>
    <div class="alert alert-danger" data-auto-dismiss="4000" style="margin:12px 16px 0; border-radius:10px;">
      <i class="fa-solid fa-exclamation-circle"></i><div><?= e($error) ?></div>
    </div>
  <?php endif; ?>

  <main class="animate-fade-in" style="padding: 12px 16px 84px;">
    <?= $content ?? '' ?>
  </main>

  <nav class="bottom-nav">
    <a href="<?= url('/shipper/dashboard') ?>" class="bottom-nav-item <?= is_active('/shipper/dashboard') ? 'active' : '' ?>">
      <i class="fa-solid fa-truck-fast"></i>
      <span>Đơn giao</span>
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
