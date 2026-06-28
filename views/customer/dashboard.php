<!-- Carousel promotions and articles -->
<div class="mb-5 animate-fade-in">
  <?php if (!empty($promotions)): ?>
    <div style="background:linear-gradient(135deg, rgba(99,102,241,0.2), rgba(139,92,246,0.2)); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px; margin-bottom: 16px; position: relative; overflow: hidden;">
      <div style="position: absolute; right: -20px; bottom: -20px; font-size: 5rem; opacity: 0.15; transform: rotate(-15deg);"><i class="fa-solid fa-gift"></i></div>
      <span class="badge badge-warning mb-2"><i class="fa-solid fa-star"></i> Khuyến mãi HOT</span>
      <?php $promo = $promotions[0]; ?>
      <h3 style="font-size: 1.1rem; font-weight:700; margin-bottom: 6px;"><?= e($promo->title) ?></h3>
      <p style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 12px;"><?= str_limit($promo->description, 120) ?></p>
      <div class="flex items-center gap-3">
        <span style="font-weight: 700; color: var(--warning); font-size: 1.1rem;"><?= $promo->type === 'percent' ? $promo->value . '%' : format_money($promo->value) ?> OFF</span>
        <span style="font-size: 0.72rem; color: var(--text-muted);">Mã: <code style="background: rgba(255,255,255,0.1); padding: 2px 6px; border-radius: 4px; color: #fff; font-weight:700;"><?= e($promo->code) ?></code></span>
      </div>
    </div>
  <?php elseif (!empty($articles)): ?>
    <div style="background:var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 18px; margin-bottom: 16px; position: relative;">
      <span class="badge badge-primary mb-2">Tin tức mới nhất</span>
      <?php $art = $articles[0]; ?>
      <h3 style="font-size: 1.1rem; font-weight:700; margin-bottom: 6px;"><?= e($art->title) ?></h3>
      <p style="font-size: 0.82rem; color: var(--text-secondary);"><?= str_limit(strip_tags($art->content), 120) ?></p>
    </div>
  <?php endif; ?>
</div>

<!-- Quick Statistics -->
<div class="grid grid-3 mb-5 animate-fade-in animate-delay-1">
  <div class="stat-card" style="padding: 14px;">
    <div class="stat-value" style="font-size: 1.4rem;"><?= $totalOrders ?></div>
    <div class="stat-label" style="font-size: 0.72rem;">Đã đặt</div>
  </div>
  <div class="stat-card" style="padding: 14px; --stat-color: var(--warning); --stat-rgb: 245,158,11;">
    <div class="stat-value" style="font-size: 1.4rem;"><?= $activeOrders ?></div>
    <div class="stat-label" style="font-size: 0.72rem;">Đang xử lý</div>
  </div>
  <div class="stat-card" style="padding: 14px; --stat-color: var(--success); --stat-rgb: 16,185,129;">
    <div class="stat-value" style="font-size: 1.4rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:100%;"><?= format_number($totalSpent) ?></div>
    <div class="stat-label" style="font-size: 0.72rem;">Đã thanh toán</div>
  </div>
</div>

<!-- Recent Orders List -->
<div class="section-header animate-fade-in animate-delay-2">
  <h2 class="section-title">Đơn hàng gần đây</h2>
  <a href="<?= url('/customer/orders') ?>" class="text-sm text-primary-color">Xem tất cả <i class="fa-solid fa-chevron-right" style="font-size:0.75rem;"></i></a>
</div>

<div class="animate-fade-in animate-delay-2">
  <?php if (empty($recentOrders)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-folder-open"></i>
      <h3>Chưa có đơn hàng nào</h3>
      <p>Các đơn hàng bạn đặt sẽ xuất hiện ở đây.</p>
    </div>
  <?php else: ?>
    <?php foreach ($recentOrders as $order): ?>
      <?php
      $progress = order_progress($order->id);
      $cStep = current_step($order->id);
      ?>
      <div class="order-card">
        <div class="order-card-header">
          <div>
            <div class="order-code"><?= e($order->order_code) ?></div>
            <div class="order-date">Hẹn trả: <?= format_date($order->due_date) ?></div>
          </div>
          <?= status_badge($order->delivery_status, 'delivery') ?>
        </div>
        
        <div class="order-progress-info">
          <span>Tiến độ sản xuất</span>
          <span><?= $progress ?>%</span>
        </div>
        <div class="progress mb-2">
          <div class="progress-bar" style="width: <?= $progress ?>%"></div>
        </div>

        <div class="flex justify-between items-center mt-3">
          <span class="text-xs text-muted">
            <?php if ($cStep): ?>
              Đang ở bước: <strong class="text-warning"><?= e($cStep->step_name) ?></strong>
            <?php else: ?>
              <span class="text-success">Sản xuất hoàn thành</span>
            <?php endif; ?>
          </span>
          <a href="<?= url("/customer/orders/{$order->id}") ?>" class="btn btn-outline btn-sm">Chi tiết</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
