<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title">Thông tin khách hàng</span>
  </div>
  <div class="card-body">
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Họ và tên bác sĩ:</span>
      <span class="font-medium text-white"><?= e($user->name) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Tên phòng khám / Nha khoa:</span>
      <span class="font-medium text-white"><?= e($user->clinic_name) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Số điện thoại:</span>
      <span class="font-medium text-primary-color"><a href="tel:<?= e($user->phone) ?>"><?= e($user->phone) ?></a></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Địa chỉ:</span>
      <span class="font-medium text-right"><?= e($user->address) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Email:</span>
      <span><?= e($user->email) ?></span>
    </div>
  </div>
</div>

<!-- Recent customer orders -->
<div class="section-header animate-fade-in">
  <h2 class="section-title">Đơn hàng gần đây</h2>
</div>

<div class="animate-fade-in">
  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-folder-open"></i>
      <h3>Không có đơn hàng nào</h3>
      <p>Bác sĩ này chưa đặt đơn hàng nào gần đây.</p>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <div class="order-card">
        <div class="order-card-header">
          <div class="order-code"><?= e($order->order_code) ?></div>
          <?= status_badge($order->delivery_status, 'delivery') ?>
        </div>
        <div class="text-xs text-secondary mt-1">Hẹn trả hàng: <?= format_date($order->due_date) ?></div>
        <div class="text-xs text-secondary">Tiến độ sản xuất: <?= order_progress($order->id) ?>%</div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
