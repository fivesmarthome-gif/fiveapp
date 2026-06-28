<div class="card mb-4 animate-fade-in">
  <div class="card-body">
    <div class="text-sm text-muted">Xin chào</div>
    <div class="font-bold text-white" style="font-size:1.2rem;"><?= e($currentUser->name ?? 'Shipper') ?></div>
    <div class="text-xs text-muted mt-1">Nhận đơn để báo khách chuẩn bị nhận hàng và bắt đầu chia sẻ vị trí.</div>
  </div>
</div>

<div class="section-header animate-fade-in">
  <h2 class="section-title">Đang giao</h2>
</div>

<?php if (empty($assignedDeliveries)): ?>
  <div class="empty-state mb-4">
    <i class="fa-solid fa-route"></i>
    <p>Chưa có đơn đang giao.</p>
  </div>
<?php else: ?>
  <?php foreach ($assignedDeliveries as $delivery): ?>
    <div class="order-card">
      <div class="order-card-header">
        <div>
          <div class="order-code"><?= e($delivery->order_code) ?></div>
          <div class="order-date"><?= e($delivery->clinic_name ?: $delivery->customer_name) ?></div>
        </div>
        <?= status_badge($delivery->status, 'delivery') ?>
      </div>
      <div class="text-sm text-muted mb-2">
        <i class="fa-solid fa-location-dot"></i>
        <?= e($delivery->delivery_address ?: $delivery->customer_address ?: 'Chưa có địa chỉ') ?>
      </div>
      <?php if ($delivery->shipper_location_updated_at): ?>
        <div class="text-xs text-primary-color mb-2">Cập nhật vị trí: <?= format_datetime($delivery->shipper_location_updated_at) ?></div>
      <?php endif; ?>
      <a href="<?= url("/shipper/deliveries/{$delivery->id}") ?>" class="btn btn-primary btn-sm" style="width:100%;">
        <i class="fa-solid fa-location-arrow"></i> Mở giao hàng
      </a>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<div class="section-header animate-fade-in" style="margin-top:24px;">
  <h2 class="section-title">Đơn chờ nhận</h2>
</div>

<?php if (empty($availableDeliveries)): ?>
  <div class="empty-state">
    <i class="fa-solid fa-box-open"></i>
    <p>Không có đơn chờ giao.</p>
  </div>
<?php else: ?>
  <?php foreach ($availableDeliveries as $delivery): ?>
    <div class="order-card">
      <div class="order-card-header">
        <div>
          <div class="order-code"><?= e($delivery->order_code) ?></div>
          <div class="order-date">Hạn: <?= format_date($delivery->adjusted_due_date ?: $delivery->due_date) ?></div>
        </div>
        <?= status_badge($delivery->status, 'delivery') ?>
      </div>
      <div class="font-medium text-white mb-1"><?= e($delivery->clinic_name ?: $delivery->customer_name) ?></div>
      <div class="text-sm text-muted mb-3">
        <i class="fa-solid fa-location-dot"></i>
        <?= e($delivery->delivery_address ?: $delivery->customer_address ?: 'Chưa có địa chỉ') ?>
      </div>
      <form action="<?= url("/shipper/deliveries/{$delivery->id}/accept") ?>" method="POST">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-success btn-sm" style="width:100%;">
          <i class="fa-solid fa-hand"></i> Nhận giao đơn này
        </button>
      </form>
    </div>
  <?php endforeach; ?>
<?php endif; ?>
