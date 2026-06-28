<!-- Filter options -->
<div class="mb-4 overflow-auto" style="white-space: nowrap; -webkit-overflow-scrolling: touch; padding-bottom: 4px;">
  <a href="<?= url('/staff/production') ?>" class="btn <?= empty($filter) ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Tất cả việc</a>
  <a href="<?= url('/staff/production?status=waiting') ?>" class="btn <?= $filter === 'waiting' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Chờ làm</a>
  <a href="<?= url('/staff/production?status=in_progress') ?>" class="btn <?= $filter === 'in_progress' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Đang làm</a>
  <a href="<?= url('/staff/production?status=completed') ?>" class="btn <?= $filter === 'completed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Đã xong</a>
</div>

<div class="animate-fade-in">
  <?php if (empty($steps)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-clipboard-check"></i>
      <h3>Không tìm thấy công việc</h3>
      <p>Không có công đoạn sản xuất nào khớp bộ lọc của bạn.</p>
    </div>
  <?php else: ?>
    <?php foreach ($steps as $step): ?>
      <div class="order-card">
        <div class="order-card-header" style="margin-bottom: 6px;">
          <div>
            <div class="order-code"><?= e($step->order_code) ?> <?= priority_badge($step->priority) ?></div>
            <div class="order-date" style="margin-top: 4px;">Khách hàng: <strong><?= e($step->clinic_name ?: $step->customer_name) ?></strong></div>
          </div>
          <span class="badge <?= $step->status === 'completed' ? 'badge-success' : ($step->status === 'in_progress' ? 'badge-warning' : 'badge-primary') ?>">
            <?= $step->status === 'completed' ? 'Hoàn thành' : ($step->status === 'in_progress' ? 'Đang làm' : 'Chờ làm') ?>
          </span>
        </div>

        <div style="font-size: 0.85rem; border-top: 1px solid var(--border-light); padding-top: 8px; margin-top: 8px;">
          Công đoạn: <strong class="text-white"><?= e($step->step_name) ?></strong> (<?= e($step->product_name) ?>)
        </div>
        <div class="text-xs text-secondary mt-1">
          Số răng: <?= e($step->tooth_numbers) ?> | Hạn giao: <span class="<?= due_date_class($step->due_date) ?> font-medium"><?= format_date($step->due_date) ?></span>
        </div>

        <div class="flex justify-end gap-2 mt-3">
          <a href="<?= url("/staff/production/{$step->id}") ?>" class="btn btn-outline btn-sm">Chi tiết</a>
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
