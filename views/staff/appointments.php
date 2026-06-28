<div class="animate-fade-in">
  <?php if (empty($appointments)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-calendar-xmark"></i>
      <h3>Không có lịch hẹn nào</h3>
      <p>Lịch hẹn tư vấn hoặc bàn giao sản phẩm sẽ xuất hiện ở đây.</p>
    </div>
  <?php else: ?>
    <?php foreach ($appointments as $app): ?>
      <div class="order-card" style="border-left: 3px solid var(--primary);">
        <div class="flex justify-between items-start">
          <div>
            <div style="font-size: 0.9rem; font-weight:700;"><?= format_datetime($app->appointment_date, 'd/m/Y H:i') ?></div>
            <div class="text-xs text-secondary mt-1">Khách hàng: <strong><?= e($app->customer_name) ?></strong></div>
            <?php if ($app->clinic_name): ?>
              <div class="text-xs text-secondary">Phòng khám: <?= e($app->clinic_name) ?></div>
            <?php endif; ?>
            <div class="text-xs text-secondary">SĐT: <a href="tel:<?= e($app->customer_phone) ?>"><?= e($app->customer_phone) ?></a></div>
            <?php if ($app->notes): ?>
              <div class="text-xs text-muted mt-2">Ghi chú: <?= e($app->notes) ?></div>
            <?php endif; ?>
          </div>
          <span class="badge badge-purple"><?= e($app->type) ?></span>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
