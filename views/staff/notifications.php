<div class="animate-fade-in">
  <?php if (empty($notifications)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-bell-slash"></i>
      <h3>Không có thông báo nào</h3>
      <p>Các thông báo phân công công việc sẽ hiển thị ở đây.</p>
    </div>
  <?php else: ?>
    <div class="divide-y border rounded-lg bg-card overflow-hidden">
      <?php foreach ($notifications as $notif): ?>
        <div class="p-3 flex gap-3 items-start <?= $notif->is_read ? '' : 'bg-hover' ?>" style="transition: background 0.2s;">
          <div class="notif-icon" style="background: <?= $notif->is_read ? 'rgba(255,255,255,0.05)' : 'rgba(99,102,241,0.15)' ?>;">
            <i class="fa-solid fa-bell"></i>
          </div>
          <div class="flex-1">
            <h4 class="text-sm font-semibold text-white mb-1"><?= e($notif->title) ?></h4>
            <p class="text-xs text-secondary mb-2"><?= e($notif->content) ?></p>
            <div class="flex items-center justify-between">
              <span class="text-xs text-muted"><?= time_ago($notif->created_at) ?></span>
              <?php if (!$notif->is_read): ?>
                <span class="badge badge-primary" style="font-size:0.6rem; padding: 1px 6px;">Mới</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
