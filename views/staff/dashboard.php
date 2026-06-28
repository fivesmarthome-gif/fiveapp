<!-- Today overview -->
<div class="grid grid-2 mb-5 animate-fade-in">
  <div class="stat-card" style="padding: 16px; --stat-color: var(--primary); --stat-rgb: 99,102,241;">
    <div class="stat-value" style="font-size: 1.6rem;"><?= $waitingCount ?></div>
    <div class="stat-label">Chờ thực hiện</div>
  </div>
  <div class="stat-card" style="padding: 16px; --stat-color: var(--warning); --stat-rgb: 245,158,11;">
    <div class="stat-value" style="font-size: 1.6rem;"><?= $inProgressCount ?></div>
    <div class="stat-label">Đang thực hiện</div>
  </div>
</div>

<div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius); padding: 14px; margin-bottom: 20px; display:flex; align-items:center; justify-content:space-between;" class="animate-fade-in">
  <div>
    <h4 style="font-size: 0.85rem; color: var(--text-secondary);">Đã hoàn thành hôm nay</h4>
    <p style="font-size: 1.2rem; font-weight:700; color: var(--success);"><?= $completedToday ?> công đoạn</p>
  </div>
  <div style="font-size: 2.2rem; opacity:0.15; color: var(--success);"><i class="fa-solid fa-circle-check"></i></div>
</div>

<!-- Assigned tasks list -->
<div class="section-header animate-fade-in animate-delay-1">
  <h2 class="section-title">Công việc được phân công</h2>
  <a href="<?= url('/staff/production') ?>" class="text-sm text-primary-color">Xem tất cả <i class="fa-solid fa-chevron-right" style="font-size:0.75rem;"></i></a>
</div>

<div class="animate-fade-in animate-delay-1">
  <?php if (empty($pendingSteps)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-circle-check"></i>
      <h3>Tuyệt vời! Đã hết việc</h3>
      <p>Không có công đoạn sản xuất nào đang chờ bạn.</p>
    </div>
  <?php else: ?>
    <?php foreach ($pendingSteps as $step): ?>
      <div class="order-card">
        <div class="order-card-header" style="margin-bottom: 6px;">
          <div>
            <div class="order-code"><?= e($step->order_code) ?> <?= priority_badge($step->priority) ?></div>
            <div class="order-date" style="margin-top: 4px;">Khách hàng: <strong><?= e($step->clinic_name ?: $step->customer_name) ?></strong></div>
          </div>
          <span class="badge <?= $step->status === 'in_progress' ? 'badge-warning' : 'badge-primary' ?>">
            <?= $step->status === 'in_progress' ? 'Đang làm' : 'Chờ làm' ?>
          </span>
        </div>

        <div style="font-size: 0.85rem; border-top: 1px solid var(--border-light); padding-top: 8px; margin-top: 8px;">
          Công đoạn: <strong class="text-white"><?= e($step->step_name) ?></strong> (<?= e($step->product_name) ?>)
        </div>
        <div class="text-xs text-secondary mt-1">
          Số răng: <?= e($step->tooth_numbers) ?> | Hạn giao: <span class="<?= due_date_class($step->due_date) ?> font-medium"><?= format_date($step->due_date) ?></span>
        </div>

        <div class="flex justify-end gap-2 mt-3">
          <a href="<?= url("/staff/production/{$step->id}") ?>" class="btn btn-outline btn-sm">Xem chi tiết</a>
          <?php if ($step->status === 'waiting'): ?>
            <form action="<?= url("/staff/production/{$step->id}/start") ?>" method="POST" style="display:inline;">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-play"></i> Bắt đầu</button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<!-- Today's appointments -->
<?php if (!empty($appointments)): ?>
<div class="section-header mt-5 animate-fade-in">
  <h2 class="section-title">Lịch hẹn hôm nay</h2>
</div>
<div class="animate-fade-in">
  <?php foreach ($appointments as $app): ?>
    <div class="order-card" style="border-left: 3px solid var(--accent);">
      <div class="flex justify-between items-start">
        <div>
          <div style="font-size: 0.9rem; font-weight:700;"><?= format_datetime($app->appointment_date, 'H:i') ?> - <?= e($app->customer_name) ?></div>
          <div class="text-xs text-secondary mt-1">SĐT: <?= e($app->customer_phone) ?></div>
          <div class="text-xs text-muted mt-1">Ghi chú: <?= e($app->notes) ?></div>
        </div>
        <span class="badge badge-purple"><?= e($app->type) ?></span>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
