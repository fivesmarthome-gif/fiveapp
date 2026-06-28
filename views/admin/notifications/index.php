<div class="section-header animate-fade-in">
  <h2 class="section-title">Thông báo & Push Notifications</h2>
  <form action="<?= url('/admin/notifications/read-all') ?>" method="POST" style="display:inline;">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-outline"><i class="fa-solid fa-check-double"></i> Đánh dấu tất cả đã đọc</button>
  </form>
</div>

<div class="grid" style="grid-template-columns: 1fr 2fr; gap: 20px;">
  <div>
    <div class="card animate-fade-in">
      <div class="card-header">
        <span class="card-title">Gửi thông báo mới</span>
      </div>
      <div class="card-body">
        <form action="<?= url('/admin/notifications/send') ?>" method="POST" id="form-notify">
          <?= csrf_field() ?>

          <div class="form-group">
            <label class="form-label" for="target">Gửi đến</label>
            <select name="target" id="target" class="form-control">
              <option value="all">Tất cả người dùng</option>
              <option value="customers">Tất cả Khách hàng</option>
              <option value="staff">Tất cả Nhân viên / KTV</option>
              <optgroup label="Khách hàng cụ thể">
                <?php foreach ($customers as $c): ?>
                  <option value="<?= $c->id ?>"><?= e($c->name) ?> - <?= e($c->clinic_name) ?></option>
                <?php endforeach; ?>
              </optgroup>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="title">Tiêu đề<span class="required">*</span></label>
            <input type="text" name="title" id="title" class="form-control" required placeholder="Nhập tiêu đề thông báo">
          </div>

          <div class="form-group">
            <label class="form-label" for="content">Nội dung<span class="required">*</span></label>
            <textarea name="content" id="content" class="form-control" rows="5" required placeholder="Nhập nội dung chi tiết..."></textarea>
          </div>

          <div class="mt-4 flex justify-end">
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-paper-plane"></i> Gửi thông báo</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div>
    <div class="card animate-fade-in animate-delay-1">
      <div class="card-header">
        <span class="card-title">Lịch sử thông báo gần đây</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($notifications)): ?>
          <div class="text-center text-muted py-6">Chưa có thông báo nào.</div>
        <?php else: ?>
          <div style="max-height: 600px; overflow-y: auto;">
            <?php foreach ($notifications as $notify): ?>
              <div style="padding: 15px 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); <?= $notify->is_read ? 'opacity: 0.7;' : '' ?>">
                <div style="display: flex; gap: 15px;">
                  <div style="width: 40px; height: 40px; border-radius: 50%; background-color: <?= $notify->is_read ? 'rgba(255, 255, 255, 0.05)' : 'rgba(115, 103, 240, 0.1)' ?>; display: flex; align-items: center; justify-content: center; color: <?= $notify->is_read ? '#a3a4cc' : '#7367f0' ?>; flex-shrink: 0;">
                    <?php if ($notify->type == 'order'): ?>
                      <i class="fa-solid fa-box"></i>
                    <?php elseif ($notify->type == 'system'): ?>
                      <i class="fa-solid fa-bell"></i>
                    <?php elseif ($notify->type == 'order_feedback'): ?>
                      <i class="fa-solid fa-comment"></i>
                    <?php else: ?>
                      <i class="fa-solid fa-circle-info"></i>
                    <?php endif; ?>
                  </div>
                  <div style="flex: 1;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 5px;">
                      <div class="font-bold <?= $notify->is_read ? 'text-white' : 'text-primary' ?>"><?= e($notify->title) ?></div>
                      <div class="text-xs text-muted" style="white-space: nowrap; margin-left: 10px;"><?= date('d/m H:i', strtotime($notify->created_at)) ?></div>
                    </div>
                    <div class="text-sm" style="color: #cfd3ec; line-height: 1.5;">
                      <?= nl2br(e($notify->content)) ?>
                    </div>
                    <?php if ($notify->receiver_name): ?>
                      <div class="text-xs mt-2" style="color: #8286a0;">
                        <i class="fa-solid fa-arrow-right"></i> Gửi đến: <?= e($notify->receiver_name) ?>
                      </div>
                    <?php else: ?>
                      <div class="text-xs mt-2" style="color: #8286a0;">
                        <i class="fa-solid fa-arrow-right"></i> Gửi đến: Quản trị viên
                      </div>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  document.getElementById('form-notify').addEventListener('submit', function(e) {
    if (!validateForm('form-notify')) {
      e.preventDefault();
      return;
    }
    
    if (!confirm('Bạn có chắc chắn muốn gửi thông báo này không? Thao tác này không thể hoàn tác.')) {
      e.preventDefault();
    }
  });
</script>
