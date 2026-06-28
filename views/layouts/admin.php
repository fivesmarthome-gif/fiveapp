<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'HoanKiem LAB') ?> | Admin</title>
  <meta name="description" content="HoanKiem LAB - Hệ thống quản lý sản xuất răng giả">
  <link rel="icon" href="<?= asset('images/favicon.png') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <?= $extraHead ?? '' ?>
</head>
<body>

<!-- Sidebar Overlay (Mobile) -->
<div id="sidebar-overlay" class="modal-overlay" style="z-index:99; background:rgba(0,0,0,0.6);"></div>

<div class="app-layout">
  <!-- Sidebar -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="logo-icon">🦷</div>
      <div class="logo-text">
        HoanKiem LAB
        <small>Admin Portal</small>
      </div>
    </div>

    <nav class="sidebar-nav">
      <div class="nav-section-label">Tổng quan</div>

      <a href="<?= url('/admin/dashboard') ?>" class="nav-item <?= active_class('/admin/dashboard') ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>

      <div class="nav-section-label">Đơn hàng</div>

      <a href="<?= url('/admin/orders') ?>" class="nav-item <?= active_class('/admin/orders') ?>">
        <i class="fa-solid fa-file-invoice"></i>
        <span>Đơn hàng</span>
      </a>

      <a href="<?= url('/admin/production') ?>" class="nav-item <?= active_class('/admin/production') ?>">
        <i class="fa-solid fa-gears"></i>
        <span>Sản xuất</span>
      </a>

      <a href="<?= url('/admin/deliveries') ?>" class="nav-item <?= active_class('/admin/deliveries') ?>">
        <i class="fa-solid fa-truck"></i>
        <span>Giao hàng</span>
      </a>

      <a href="<?= url('/admin/appointments') ?>" class="nav-item <?= active_class('/admin/appointments') ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>Lịch hẹn</span>
      </a>

      <div class="nav-section-label">Người dùng</div>

      <a href="<?= url('/admin/customers') ?>" class="nav-item <?= active_class('/admin/customers') ?>">
        <i class="fa-solid fa-user-doctor"></i>
        <span>Khách hàng</span>
      </a>

      <a href="<?= url('/admin/staff') ?>" class="nav-item <?= active_class('/admin/staff') ?>">
        <i class="fa-solid fa-users-gear"></i>
        <span>Nhân viên</span>
      </a>

      <div class="nav-section-label">Tài chính</div>

      <a href="<?= url('/admin/revenue') ?>" class="nav-item <?= active_class('/admin/revenue') ?>">
        <i class="fa-solid fa-chart-line"></i>
        <span>Doanh thu</span>
      </a>

      <div class="nav-section-label">Kho & Vật liệu</div>

      <a href="<?= url('/admin/materials') ?>" class="nav-item <?= active_class('/admin/materials') ?>">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>Vật liệu</span>
      </a>

      <a href="<?= url('/admin/product-types') ?>" class="nav-item <?= active_class('/admin/product-types') ?>">
        <i class="fa-solid fa-tooth"></i>
        <span>Loại sản phẩm</span>
      </a>

      <div class="nav-section-label">Nội dung</div>

      <a href="<?= url('/admin/articles') ?>" class="nav-item <?= active_class('/admin/articles') ?>">
        <i class="fa-solid fa-newspaper"></i>
        <span>Bài viết / Tin tức</span>
      </a>

      <a href="<?= url('/admin/promotions') ?>" class="nav-item <?= active_class('/admin/promotions') ?>">
        <i class="fa-solid fa-gift"></i>
        <span>Khuyến mãi</span>
      </a>

      <a href="<?= url('/admin/branches') ?>" class="nav-item <?= active_class('/admin/branches') ?>">
        <i class="fa-solid fa-hospital"></i>
        <span>Chi nhánh</span>
      </a>

      <div class="nav-section-label">Hệ thống</div>

      <a href="<?= url('/admin/feedbacks') ?>" class="nav-item <?= active_class('/admin/feedbacks') ?>">
        <i class="fa-solid fa-comments"></i>
        <span>Phản hồi</span>
        <?php
        $pendingFeedbacks = $db->count('order_feedbacks', "status = 'pending'");
        if ($pendingFeedbacks > 0): ?>
        <span class="nav-badge"><?= $pendingFeedbacks ?></span>
        <?php endif; ?>
      </a>

      <a href="<?= url('/admin/notifications') ?>" class="nav-item <?= active_class('/admin/notifications') ?>">
        <i class="fa-solid fa-bell"></i>
        <span>Thông báo</span>
      </a>

      <a href="<?= url('/admin/settings') ?>" class="nav-item <?= active_class('/admin/settings') ?>">
        <i class="fa-solid fa-gear"></i>
        <span>Cài đặt</span>
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <img src="<?= avatar_url($currentUser->avatar ?? null) ?>" alt="Avatar" class="avatar">
        <div class="user-info">
          <div class="user-name"><?= e($currentUser->name ?? 'Admin') ?></div>
          <div class="user-role">Quản trị viên</div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <div class="main-content">
    <!-- Topbar -->
    <header class="topbar">
      <button id="sidebar-toggle" class="btn-icon" style="display:none;" title="Menu">
        <i class="fa-solid fa-bars"></i>
      </button>

      <div class="topbar-left">
        <div class="page-title"><?= e($pageTitle ?? 'Dashboard') ?></div>
        <?php if (!empty($breadcrumbs)): ?>
        <div class="breadcrumb">
          <?php foreach ($breadcrumbs as $label => $link): ?>
            <?php if ($link): ?>
              <a href="<?= $link ?>"><?= e($label) ?></a>
              <span>/</span>
            <?php else: ?>
              <span><?= e($label) ?></span>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <div class="topbar-right">
        <!-- Notifications -->
        <div class="dropdown">
          <button class="topbar-btn" id="notif-btn" title="Thông báo">
            <i class="fa-solid fa-bell"></i>
            <?php if ($unreadNotifications > 0): ?>
              <span class="topbar-badge" id="notif-dot"></span>
            <?php endif; ?>
          </button>

          <div class="dropdown-menu" id="notif-dropdown">
            <div class="card-header">
              <span class="card-title">Thông báo</span>
              <?php if ($unreadNotifications > 0): ?>
              <a href="<?= url('/admin/notifications') ?>" class="text-sm text-primary-color">
                Đánh dấu tất cả đã đọc
              </a>
              <?php endif; ?>
            </div>

            <?php
            $recentNotifs = $db->fetchAll(
              "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8",
              [$currentUser->id]
            );
            ?>

            <?php if (empty($recentNotifs)): ?>
            <div class="empty-state" style="padding: 30px 20px;">
              <i class="fa-solid fa-bell-slash"></i>
              <p>Không có thông báo</p>
            </div>
            <?php else: ?>
            <div class="divide-y">
              <?php foreach ($recentNotifs as $notif): ?>
              <div class="notification-item <?= $notif->is_read ? '' : 'unread' ?>">
                <div class="notif-icon">
                  <i class="fa-solid fa-<?= $notif->type === 'order_status' ? 'file-invoice' : ($notif->type === 'promotion' ? 'gift' : 'bell') ?>"></i>
                </div>
                <div class="notif-content">
                  <div class="notif-title"><?= e($notif->title) ?></div>
                  <div class="notif-text"><?= e($notif->content) ?></div>
                  <div class="notif-time"><?= time_ago($notif->created_at) ?></div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <div class="card-footer text-center">
              <a href="<?= url('/admin/notifications') ?>" class="text-sm text-primary-color">Xem tất cả</a>
            </div>
          </div>
        </div>

        <!-- Profile -->
        <a href="<?= url('/admin/profile') ?>" class="topbar-btn" title="Hồ sơ">
          <i class="fa-solid fa-user"></i>
        </a>

        <!-- Logout -->
        <form action="<?= url('/logout') ?>" method="POST" style="display:inline;">
          <?= csrf_field() ?>
          <button type="submit" class="topbar-btn" title="Đăng xuất">
            <i class="fa-solid fa-sign-out-alt"></i>
          </button>
        </form>
      </div>
    </header>

    <!-- Page Body -->
    <main class="page-body animate-fade-in">
      <!-- Flash Messages -->
      <?php $success = get_flash('success'); if ($success): ?>
        <div class="alert alert-success mb-4" data-auto-dismiss="4000">
          <i class="fa-solid fa-check-circle"></i>
          <div><?= e($success) ?></div>
          <button class="btn-close" style="margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; font-size:1.1rem;">×</button>
        </div>
      <?php endif; ?>
      <?php $error = get_flash('error'); if ($error): ?>
        <div class="alert alert-danger mb-4" data-auto-dismiss="5000">
          <i class="fa-solid fa-exclamation-circle"></i>
          <div><?= e($error) ?></div>
          <button class="btn-close" style="margin-left:auto; background:none; border:none; cursor:pointer; color:inherit; font-size:1.1rem;">×</button>
        </div>
      <?php endif; ?>

      <?= $content ?? '' ?>
    </main>
  </div>
</div>

<script>
  var baseUrl = '<?= url('') ?>';
</script>
<script src="<?= asset('js/app.js') ?>"></script>
<?= $extraScripts ?? '' ?>
</body>
</html>
