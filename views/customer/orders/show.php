<?php
// =====================================================
// Delivery tracker: xác định bước hiện tại
// =====================================================
$deliveryStatus = $order->delivery_status ?? 'none';
$productionStatus = $order->production_status ?? 'pending';

// 5 bước tổng thể của đơn hàng
$orderJourney = [
    [
        'key'   => 'ordered',
        'label' => 'Đặt hàng',
        'icon'  => 'fa-file-pen',
        'color' => '#7367f0',
        'done'  => true, // always done if order exists
        'time'  => $order->created_at,
    ],
    [
        'key'   => 'in_production',
        'label' => 'Đang sản xuất',
        'icon'  => 'fa-gears',
        'color' => '#f59e0b',
        'done'  => in_array($productionStatus, ['confirmed','in_production','qc_passed','ready']),
        'time'  => null,
    ],
    [
        'key'   => 'ready',
        'label' => 'Sẵn sàng giao',
        'icon'  => 'fa-box-check',
        'color' => '#10b981',
        'done'  => in_array($productionStatus, ['qc_passed','ready']) || in_array($deliveryStatus, ['waiting_pickup','shipping','delivered','completed']),
        'time'  => null,
    ],
    [
        'key'   => 'shipping',
        'label' => 'Đang giao hàng',
        'icon'  => 'fa-truck-fast',
        'color' => '#3b82f6',
        'done'  => in_array($deliveryStatus, ['shipping','delivered','completed']),
        'time'  => ($delivery && $delivery->shipped_at) ? $delivery->shipped_at : null,
    ],
    [
        'key'   => 'delivered',
        'label' => 'Đã nhận hàng',
        'icon'  => 'fa-circle-check',
        'color' => '#10b981',
        'done'  => in_array($deliveryStatus, ['delivered','completed']) || $order->overall_status === 'completed',
        'time'  => ($delivery && $delivery->delivered_at) ? $delivery->delivered_at : null,
    ],
];

// Tìm active step (step đầu tiên chưa done)
$activeStep = null;
foreach ($orderJourney as $idx => $step) {
    if (!$step['done']) {
        $activeStep = $idx;
        break;
    }
}
if ($activeStep === null) $activeStep = count($orderJourney) - 1; // All done
?>

<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title">Mã đơn: <?= e($order->order_code) ?></span>
    <?= status_badge($order->delivery_status ?? 'none', 'delivery') ?>
  </div>
  <div class="card-body">
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Ngày đặt:</span>
      <span class="font-medium"><?= format_date($order->created_at) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Hẹn trả hàng:</span>
      <span class="font-medium text-primary-color"><?= format_date($order->due_date) ?></span>
    </div>
    <?php if ($order->adjusted_due_date): ?>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Ngày trả điều chỉnh:</span>
      <span class="font-medium text-warning"><?= format_date($order->adjusted_due_date) ?></span>
    </div>
    <?php endif; ?>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Độ ưu tiên:</span>
      <span><?= priority_badge($order->priority) ?></span>
    </div>
    <div class="flex justify-between">
      <span class="text-secondary">Sản xuất:</span>
      <span><?= status_badge($order->production_status, 'production') ?></span>
    </div>
  </div>
</div>

<!-- ========== ORDER JOURNEY TRACKER ========== -->
<div class="card mb-4 animate-fade-in animate-delay-1" style="overflow: hidden;">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-route"></i> Hành trình đơn hàng</span>
  </div>
  <div class="card-body" style="padding: 24px 16px 8px;">
    <div style="display: flex; justify-content: space-between; position: relative;">
      <!-- Background connector line -->
      <div style="position: absolute; top: 18px; left: 9%; right: 9%; height: 2px; background: rgba(255,255,255,0.07); z-index: 0;"></div>
      <!-- Progress line (filled) -->
      <?php
        $doneCount = count(array_filter($orderJourney, fn($s) => $s['done']));
        $progressPct = ($doneCount / (count($orderJourney) - 1)) * 100;
        $progressPct = min(100, max(0, $progressPct));
      ?>
      <div style="position: absolute; top: 18px; left: 9%; width: calc(<?= $progressPct ?>% * 0.82); height: 2px; background: linear-gradient(to right, #7367f0, #10b981); z-index: 0; transition: width 0.6s;"></div>

      <?php foreach ($orderJourney as $idx => $step):
        $isDone   = $step['done'];
        $isActive = ($idx === $activeStep);
        $isPast   = $isDone && !$isActive;
      ?>
        <div style="flex: 1; text-align: center; z-index: 1; padding: 0 4px;">
          <div style="
            width: 38px; height: 38px; border-radius: 50%; margin: 0 auto 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.9rem;
            background: <?= $isDone ? "linear-gradient(135deg, {$step['color']}, {$step['color']}cc)" : 'rgba(255,255,255,0.06)' ?>;
            border: 2px solid <?= $isDone ? $step['color'] : 'rgba(255,255,255,0.1)' ?>;
            color: <?= $isDone ? 'white' : '#6c7293' ?>;
            <?= ($isActive && !$isDone) ? "box-shadow: 0 0 0 4px {$step['color']}33;" : '' ?>
            <?= $isDone ? "box-shadow: 0 0 12px {$step['color']}44;" : '' ?>
          ">
            <?php if ($isDone && !$isActive): ?>
              <i class="fa-solid fa-check" style="font-size: 0.8rem;"></i>
            <?php else: ?>
              <i class="fa-solid <?= $step['icon'] ?>"></i>
            <?php endif; ?>
          </div>
          <div style="
            font-size: 0.75rem; font-weight: <?= $isActive ? '700' : '500' ?>;
            color: <?= $isDone ? '#e4e6f4' : '#6c7293' ?>;
            line-height: 1.3;
          "><?= $step['label'] ?></div>
          <?php if ($step['time']): ?>
            <div style="font-size: 0.65rem; color: <?= $step['color'] ?>; margin-top: 4px; opacity: 0.9;">
              <?= format_datetime($step['time']) ?>
            </div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="height: 16px;"></div>
  </div>
</div>

<!-- ========== PRODUCTION STEPS ========== -->
<div class="card mb-4 animate-fade-in animate-delay-2">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-gears"></i> Tiến độ sản xuất (<?= order_progress($order->id) ?>%)</span>
  </div>
  <div class="card-body" style="padding: 16px 8px;">
    <div class="steps-tracker">
      <?php
      $orderSteps = $db->fetchAll("SELECT * FROM production_steps WHERE order_id = ? ORDER BY step_number", [$order->id]);
      ?>
      <?php if (empty($orderSteps)): ?>
        <div class="text-center text-muted w-full p-3">Đang chờ khởi tạo quy trình sản xuất...</div>
      <?php else: ?>
        <?php foreach ($orderSteps as $step): ?>
          <div class="step-item <?= $step->status === 'completed' ? 'completed' : ($step->status === 'in_progress' ? 'in-progress' : ($step->status === 'rework' ? 'rework' : '')) ?>">
            <div class="step-circle">
              <?php if ($step->status === 'completed'): ?>
                <i class="fa-solid fa-check"></i>
              <?php elseif ($step->status === 'rework'): ?>
                <i class="fa-solid fa-rotate-left" style="font-size: 0.7rem;"></i>
              <?php else: ?>
                <?= $step->step_number ?>
              <?php endif; ?>
            </div>
            <div class="step-label"><?= e($step->step_name) ?></div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ========== DELIVERY INFO CARD ========== -->
<?php if ($delivery && $delivery->status !== 'pending'): ?>
<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-truck"></i> Thông tin giao nhận</span>
    <?= status_badge($delivery->status, 'delivery') ?>
  </div>
  <div class="card-body">
    <?php if ($delivery->courier_name || $delivery->tracking_number): ?>
    <div style="background: rgba(115,103,240,0.08); border: 1px solid rgba(115,103,240,0.2); border-radius: 10px; padding: 14px 16px; margin-bottom: 16px;">
      <?php if ($delivery->courier_name): ?>
        <div class="flex justify-between mb-2">
          <span class="text-secondary text-sm"><i class="fa-solid fa-truck-fast"></i> Hãng vận chuyển:</span>
          <span class="font-bold text-white"><?= e($delivery->courier_name) ?></span>
        </div>
      <?php endif; ?>
      <?php if ($delivery->tracking_number): ?>
        <div class="flex justify-between mb-2">
          <span class="text-secondary text-sm"><i class="fa-solid fa-barcode"></i> Mã vận đơn:</span>
          <span class="font-bold text-primary" style="font-family: monospace; font-size: 1rem; letter-spacing: 1px;"><?= e($delivery->tracking_number) ?></span>
        </div>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="divide-y text-sm">
      <?php if ($delivery->shipped_at): ?>
      <div class="flex justify-between py-2">
        <span class="text-secondary"><i class="fa-solid fa-calendar-check"></i> Ngày gửi:</span>
        <span><?= format_datetime($delivery->shipped_at) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($delivery->delivered_at): ?>
      <div class="flex justify-between py-2">
        <span class="text-secondary"><i class="fa-solid fa-circle-check text-success"></i> Ngày giao:</span>
        <span class="text-success font-bold"><?= format_datetime($delivery->delivered_at) ?></span>
      </div>
      <?php endif; ?>
      <?php if ($delivery->notes): ?>
      <div class="py-2">
        <div class="text-secondary mb-1"><i class="fa-solid fa-note-sticky"></i> Ghi chú:</div>
        <p class="text-sm" style="color: #c5c7dd; line-height: 1.5;"><?= e($delivery->notes) ?></p>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($delivery->proof_photo): ?>
    <div class="mt-3">
      <div class="text-secondary text-sm mb-2"><i class="fa-solid fa-camera"></i> Ảnh xác nhận giao hàng:</div>
      <img src="<?= url('/' . ltrim($delivery->proof_photo, '/')) ?>" alt="Ảnh xác nhận" style="width: 100%; border-radius: 8px; max-height: 200px; object-fit: cover;">
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ========== PRODUCTS LIST ========== -->
<div class="card mb-4 animate-fade-in">
  <div class="card-header"><span class="card-title"><i class="fa-solid fa-tooth"></i> Chi tiết sản phẩm</span></div>
  <div class="card-body p-0">
    <div class="divide-y">
      <?php foreach ($items as $item): ?>
        <div class="p-3">
          <div class="flex justify-between mb-1">
            <span class="font-semibold text-white"><?= e($item->product_name) ?></span>
            <span class="text-secondary">SL: <?= $item->quantity ?></span>
          </div>
          <div class="text-xs text-secondary mb-1">
            Màu răng: <strong class="text-white"><?= e($item->shade) ?></strong>
            | Số răng: <strong class="text-white"><?= e($item->tooth_numbers) ?></strong>
          </div>
          <?php if ($item->specifications): ?>
            <div class="text-xs text-muted">Yêu cầu: <?= e($item->specifications) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ========== ACTIONS ========== -->
<?php if ($order->delivery_status === 'delivered'): ?>
  <div class="flex gap-3 mb-4 animate-fade-in">
    <form action="<?= url("/customer/orders/{$order->id}/confirm") ?>" method="POST" class="flex-1">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-success btn-block" style="width: 100%;">
        <i class="fa-solid fa-circle-check"></i> Xác nhận đã nhận hàng
      </button>
    </form>
    <button onclick="openModal('modal-return')" class="btn btn-outline flex-1">
      <i class="fa-solid fa-undo"></i> Yêu cầu hoàn trả
    </button>
  </div>
<?php endif; ?>

<?php if (in_array($order->delivery_status, ['delivered','completed']) || $order->overall_status === 'completed'): ?>
  <?php if (empty($feedbacks)): ?>
  <div class="card mb-4 animate-fade-in">
    <div class="card-header"><span class="card-title"><i class="fa-solid fa-star"></i> Đánh giá chất lượng</span></div>
    <div class="card-body">
      <form action="<?= url("/customer/orders/{$order->id}/feedback") ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <div class="form-group">
          <label class="form-label">Đánh giá sao</label>
          <div id="star-rating" style="display: flex; gap: 8px; margin-bottom: 12px;">
            <?php for ($s = 1; $s <= 5; $s++): ?>
              <label style="cursor: pointer; font-size: 1.5rem; color: #6c7293;" title="<?= $s ?> sao">
                <input type="radio" name="rating" value="<?= $s ?>" style="display: none;" required>
                <i class="fa-star fa-regular" data-star="<?= $s ?>"></i>
              </label>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-group">
          <textarea name="content" class="form-control" placeholder="Nhập ý kiến về sản phẩm, chất lượng, dịch vụ..." required style="height: 80px;"></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Ảnh chụp thực tế (nếu có)</label>
          <input type="file" name="images[]" multiple class="form-control" accept="image/*">
        </div>
        <button type="submit" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane"></i> Gửi đánh giá</button>
      </form>
    </div>
  </div>
  <?php else: ?>
  <!-- Show existing feedbacks -->
  <div class="card mb-4 animate-fade-in">
    <div class="card-header"><span class="card-title"><i class="fa-solid fa-comments"></i> Phản hồi của bạn</span></div>
    <div class="card-body p-0">
      <div class="divide-y">
        <?php foreach ($feedbacks as $fb): ?>
          <div class="p-3">
            <div class="flex justify-between text-xs text-muted mb-1">
              <span>Phản hồi <?= format_datetime($fb->created_at) ?></span>
              <?php if ($fb->status === 'resolved'): ?>
                <span class="badge badge-success">Đã xử lý</span>
              <?php else: ?>
                <span class="badge badge-warning">Chờ xử lý</span>
              <?php endif; ?>
            </div>
            <p class="text-sm text-white mb-2"><?= e($fb->content) ?></p>
            <?php if ($fb->admin_reply): ?>
              <div style="background: rgba(115,103,240,0.1); border-left: 3px solid #7367f0; padding: 8px 12px; border-radius: 0 6px 6px 0; margin-top: 8px;">
                <div class="text-xs text-primary mb-1"><i class="fa-solid fa-headset"></i> HoanKiem LAB phản hồi:</div>
                <p class="text-xs" style="color: #c5c7dd;"><?= e($fb->admin_reply) ?></p>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
<?php endif; ?>

<!-- ========== RETURN MODAL ========== -->
<div class="modal-overlay" id="modal-return">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Yêu cầu hoàn trả / Bảo hành</span>
      <button class="btn-icon" onclick="closeModal('modal-return')">×</button>
    </div>
    <form action="<?= url("/customer/orders/{$order->id}/return") ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Lý do yêu cầu làm lại / Hoàn trả</label>
          <textarea name="reason" class="form-control" placeholder="Vui lòng nêu rõ lý do hoặc các chi tiết kỹ thuật cần mài sửa/làm lại..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-return')">Huỷ</button>
        <button type="submit" class="btn btn-danger">Gửi yêu cầu</button>
      </div>
    </form>
  </div>
</div>

<script>
// Star rating interaction
document.querySelectorAll('#star-rating label').forEach(function(label, idx) {
  label.addEventListener('click', function() {
    const stars = document.querySelectorAll('#star-rating label i');
    const radio = this.querySelector('input');
    radio.checked = true;
    stars.forEach(function(star, i) {
      star.className = i <= idx ? 'fa-star fa-solid' : 'fa-star fa-regular';
      star.style.color = i <= idx ? '#f59e0b' : '#6c7293';
    });
  });
  label.addEventListener('mouseover', function() {
    const stars = document.querySelectorAll('#star-rating label i');
    stars.forEach(function(star, i) {
      if (i <= idx) star.style.color = '#f59e0b';
      else star.style.color = '#6c7293';
    });
  });
  label.addEventListener('mouseout', function() {
    const checked = document.querySelector('#star-rating input:checked');
    const stars = document.querySelectorAll('#star-rating label i');
    if (checked) {
      const val = parseInt(checked.value);
      stars.forEach(function(star, i) {
        star.className = i < val ? 'fa-star fa-solid' : 'fa-star fa-regular';
        star.style.color = i < val ? '#f59e0b' : '#6c7293';
      });
    } else {
      stars.forEach(star => star.style.color = '#6c7293');
    }
  });
});
</script>
