<?php
// Delivery status map
$deliverySteps = [
    ['key' => 'waiting_pickup', 'label' => 'Chờ lấy hàng',     'icon' => 'fa-box',           'desc' => 'Đơn hàng sẵn sàng, chờ shipper đến lấy'],
    ['key' => 'shipping',       'label' => 'Đang vận chuyển',   'icon' => 'fa-truck-moving',   'desc' => 'Đơn hàng đang trên đường giao'],
    ['key' => 'delivered',      'label' => 'Đã giao hàng',      'icon' => 'fa-circle-check',   'desc' => 'Hàng đã đến tay phòng khám'],
];
$returnSteps = [
    ['key' => 'pending_return', 'label' => 'Chờ duyệt hoàn',   'icon' => 'fa-clock-rotate-left', 'desc' => 'Khách yêu cầu trả lại mẫu'],
    ['key' => 'returned',       'label' => 'Đã hoàn trả',      'icon' => 'fa-rotate-left',        'desc' => 'Mẫu đã về đến Lab'],
];

$currentStatus = $delivery->status;
$isReturn = in_array($currentStatus, ['pending_return', 'returned']);

function getStepState(string $key, string $current, array $steps): string {
    $keys = array_column($steps, 'key');
    $ci = array_search($current, $keys);
    $ki = array_search($key, $keys);
    if ($ki === false) return 'pending';
    if ($ki < $ci) return 'completed';
    if ($ki === $ci) return 'active';
    return 'pending';
}
?>

<div class="section-header animate-fade-in" style="margin-bottom: 24px;">
  <div>
    <h2 class="section-title">Vận đơn #<?= e($delivery->order_code) ?></h2>
    <div class="text-sm text-muted mt-1">Khách: <strong class="text-white"><?= e($delivery->clinic_name) ?></strong> — <?= e($delivery->customer_name) ?></div>
  </div>
  <div class="flex gap-3">
    <?= status_badge($currentStatus, 'delivery') ?>
    <a href="<?= url('/admin/orders/' . $delivery->order_id) ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-invoice"></i> Xem đơn hàng</a>
    <a href="<?= url('/admin/deliveries') ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
  </div>
</div>

<!-- Delivery Progress Tracker -->
<div class="card animate-fade-in" style="margin-bottom: 24px; padding: 28px 32px;">
  <div style="position: relative;">
    <!-- Line connector -->
    <div style="position: absolute; top: 22px; left: calc(100% / <?= count($deliverySteps) ?> / 2); right: calc(100% / <?= count($deliverySteps) ?> / 2); height: 3px; background: rgba(255,255,255,0.08); z-index: 0;"></div>

    <div style="display: flex; justify-content: space-between; position: relative; z-index: 1;">
      <?php foreach ($deliverySteps as $step):
        $state = getStepState($step['key'], $currentStatus, $deliverySteps);
        if ($isReturn) $state = ($step['key'] === 'delivered') ? 'completed' : ($state === 'active' ? 'completed' : $state);
      ?>
        <div style="flex: 1; text-align: center; padding: 0 8px;">
          <div style="
            width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
            background: <?= $state === 'completed' ? 'linear-gradient(135deg, #10b981, #059669)' : ($state === 'active' ? 'linear-gradient(135deg, #7367f0, #a59ef5)' : 'rgba(255,255,255,0.06)') ?>;
            border: 2px solid <?= $state === 'completed' ? '#10b981' : ($state === 'active' ? '#7367f0' : 'rgba(255,255,255,0.1)') ?>;
            color: <?= $state === 'pending' ? '#6c7293' : 'white' ?>;
            <?= $state === 'active' ? 'box-shadow: 0 0 20px rgba(115,103,240,0.5);' : '' ?>
            transition: all 0.3s;
          ">
            <?php if ($state === 'completed'): ?>
              <i class="fa-solid fa-check"></i>
            <?php else: ?>
              <i class="fa-solid <?= $step['icon'] ?>"></i>
            <?php endif; ?>
          </div>
          <div style="font-size: 0.82rem; font-weight: <?= $state === 'active' ? '700' : '500' ?>; color: <?= $state === 'pending' ? '#6c7293' : ($state === 'active' ? '#a59ef5' : '#e4e6f4') ?>;"><?= $step['label'] ?></div>
          <div style="font-size: 0.73rem; color: #6c7293; margin-top: 4px; line-height: 1.3;"><?= $step['desc'] ?></div>
          <?php
            // Timestamp for each step
            if ($step['key'] === 'waiting_pickup' && $delivery->order_created_at):
          ?>
            <div style="font-size: 0.7rem; color: #7367f0; margin-top: 6px;"><?= format_datetime($delivery->created_at) ?></div>
          <?php elseif ($step['key'] === 'shipping' && $delivery->shipped_at): ?>
            <div style="font-size: 0.7rem; color: #7367f0; margin-top: 6px;"><?= format_datetime($delivery->shipped_at) ?></div>
          <?php elseif ($step['key'] === 'delivered' && $delivery->delivered_at): ?>
            <div style="font-size: 0.7rem; color: #10b981; margin-top: 6px;"><?= format_datetime($delivery->delivered_at) ?></div>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>

      <?php if ($isReturn): foreach ($returnSteps as $step):
        $state = getStepState($step['key'], $currentStatus, $returnSteps); ?>
        <div style="flex: 1; text-align: center; padding: 0 8px;">
          <div style="
            width: 44px; height: 44px; border-radius: 50%; margin: 0 auto 12px;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
            background: <?= $state === 'completed' ? 'linear-gradient(135deg, #6366f1, #4f46e5)' : ($state === 'active' ? 'linear-gradient(135deg, #ef4444, #dc2626)' : 'rgba(255,255,255,0.06)') ?>;
            border: 2px solid <?= $state === 'completed' ? '#6366f1' : ($state === 'active' ? '#ef4444' : 'rgba(255,255,255,0.1)') ?>;
            color: <?= $state === 'pending' ? '#6c7293' : 'white' ?>;
          ">
            <i class="fa-solid <?= $step['icon'] ?>"></i>
          </div>
          <div style="font-size: 0.82rem; font-weight: 600; color: #e4e6f4;"><?= $step['label'] ?></div>
          <div style="font-size: 0.73rem; color: #6c7293; margin-top: 4px;"><?= $step['desc'] ?></div>
        </div>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 1fr; gap: 20px;">

  <!-- LEFT: Update Form -->
  <div>
    <div class="card animate-fade-in animate-delay-1">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-truck-fast"></i> Cập nhật vận đơn</span>
      </div>
      <div class="card-body">
        <!-- Delivery info summary -->
        <div style="background: rgba(255,255,255,0.04); border-radius: 10px; padding: 14px 16px; margin-bottom: 20px; font-size: 0.875rem;">
          <div class="flex justify-between py-1">
            <span class="text-muted">Địa chỉ giao:</span>
            <span class="text-white font-medium text-right" style="max-width: 60%;"><?= e($delivery->delivery_address ?: $delivery->customer_address) ?></span>
          </div>
          <div class="flex justify-between py-1">
            <span class="text-muted">Người nhận:</span>
            <span class="text-white"><?= e($delivery->recipient_name ?: $delivery->customer_name) ?> | <?= e($delivery->recipient_phone ?: $delivery->customer_phone) ?></span>
          </div>
          <div class="flex justify-between py-1">
            <span class="text-muted">Hạn giao:</span>
            <span class="<?= due_date_class($delivery->due_date) ?> font-bold"><?= format_date($delivery->adjusted_due_date ?: $delivery->due_date) ?></span>
          </div>
          <?php if ($delivery->tracking_number): ?>
          <div class="flex justify-between py-1">
            <span class="text-muted">Mã vận đơn:</span>
            <span class="text-primary font-bold"><?= e($delivery->tracking_number) ?></span>
          </div>
          <?php endif; ?>
        </div>

        <form action="<?= url("/admin/deliveries/{$delivery->id}/update") ?>" method="POST" enctype="multipart/form-data">
          <?= csrf_field() ?>

          <div class="form-group">
            <label class="form-label" for="status">Trạng thái vận chuyển</label>
            <select name="status" id="status" class="form-control">
              <option value="waiting_pickup" <?= $currentStatus === 'waiting_pickup' ? 'selected' : '' ?>>⏳ Chờ shipper lấy hàng</option>
              <option value="shipping"       <?= $currentStatus === 'shipping'       ? 'selected' : '' ?>>🚚 Đang vận chuyển</option>
              <option value="delivered"      <?= $currentStatus === 'delivered'      ? 'selected' : '' ?>>✅ Giao hàng thành công</option>
              <option value="pending_return" <?= $currentStatus === 'pending_return' ? 'selected' : '' ?>>↩️ Chờ duyệt hoàn trả</option>
              <option value="returned"       <?= $currentStatus === 'returned'       ? 'selected' : '' ?>>🔄 Đã hoàn trả về Lab</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="delivered_by">Nhân viên giao hàng (Nội bộ)</label>
            <select name="delivered_by" id="delivered_by" class="form-control">
              <option value="">-- Chưa phân công --</option>
              <?php foreach ($staffList as $s): ?>
                <option value="<?= $s->id ?>" <?= $s->id == $delivery->delivered_by ? 'selected' : '' ?>><?= e($s->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="flex gap-3">
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="courier_name">Hãng vận chuyển</label>
              <input type="text" name="courier_name" id="courier_name" class="form-control" value="<?= e($delivery->courier_name) ?>" placeholder="GHTK, Viettel Post...">
            </div>
            <div class="form-group" style="flex: 1;">
              <label class="form-label" for="tracking_number">Mã vận đơn</label>
              <input type="text" name="tracking_number" id="tracking_number" class="form-control" value="<?= e($delivery->tracking_number) ?>" placeholder="Mã tracking">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="notes">Ghi chú giao nhận</label>
            <textarea name="notes" id="notes" class="form-control" style="height: 70px;" placeholder="SĐT shipper, lưu ý giao nhận..."><?= e($delivery->notes) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="proof_photo">Ảnh xác nhận giao hàng</label>
            <input type="file" name="proof_photo" id="proof_photo" class="form-control" accept="image/*" onchange="previewProof(this)">
            <?php if ($delivery->proof_photo): ?>
              <div class="mt-2">
                <img src="<?= url('/' . ltrim($delivery->proof_photo, '/')) ?>" alt="Ảnh xác nhận" style="width: 100%; max-height: 160px; object-fit: cover; border-radius: 8px; border: 1px solid rgba(255,255,255,0.1);">
              </div>
            <?php endif; ?>
            <div id="proof-preview" style="display: none; width: 100%; border-radius: 8px; overflow: hidden; margin-top: 8px;">
              <img id="proof-img" src="" style="width: 100%; max-height: 160px; object-fit: cover;">
            </div>
          </div>

          <div class="flex justify-end gap-3 mt-4">
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Cập nhật vận đơn</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- RIGHT: Timeline & Info -->
  <div>
    <!-- Order info card -->
    <div class="card animate-fade-in animate-delay-1" style="margin-bottom: 20px;">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-circle-info"></i> Thông tin khách hàng</span>
      </div>
      <div class="card-body">
        <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 16px;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #7367f0, #a59ef5); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: white; font-weight: bold; flex-shrink: 0;">
            <?= strtoupper(mb_substr($delivery->customer_name, 0, 1)) ?>
          </div>
          <div>
            <div class="font-bold text-white"><?= e($delivery->customer_name) ?></div>
            <div class="text-sm text-muted"><?= e($delivery->clinic_name) ?></div>
            <div class="text-sm"><i class="fa-solid fa-phone text-primary" style="font-size: 0.75rem;"></i> <?= e($delivery->customer_phone) ?></div>
          </div>
        </div>
        <div style="font-size: 0.85rem; color: #a3a4cc; background: rgba(255,255,255,0.04); padding: 10px 12px; border-radius: 8px;">
          <i class="fa-solid fa-location-dot text-primary"></i>
          <?= e($delivery->customer_address ?: 'Chưa có địa chỉ') ?>
        </div>
        <div class="mt-3 flex gap-2">
          <a href="<?= url("/admin/customers/{$delivery->customer_id}") ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-user"></i> Hồ sơ KH</a>
          <a href="<?= url("/admin/orders/{$delivery->order_id}") ?>" class="btn btn-outline btn-sm"><i class="fa-solid fa-file-invoice"></i> Chi tiết đơn</a>
        </div>
      </div>
    </div>

    <!-- Activity Timeline -->
    <div class="card animate-fade-in animate-delay-2">
      <div class="card-header">
        <span class="card-title"><i class="fa-solid fa-timeline"></i> Lịch sử hoạt động</span>
      </div>
      <div class="card-body p-0">
        <?php if (empty($allLogs)): ?>
          <div class="text-center text-muted py-6">Chưa có lịch sử cập nhật.</div>
        <?php else: ?>
          <div style="padding: 8px 0; max-height: 420px; overflow-y: auto;">
            <?php foreach ($allLogs as $log): 
              $typeColor = $log->status_type === 'delivery' ? '#7367f0' : ($log->status_type === 'production' ? '#f59e0b' : '#a3a4cc');
              $typeIcon  = $log->status_type === 'delivery' ? 'fa-truck' : ($log->status_type === 'production' ? 'fa-gears' : 'fa-circle-dot');
            ?>
            <div style="display: flex; gap: 14px; padding: 10px 20px; border-bottom: 1px solid rgba(255,255,255,0.04); align-items: flex-start;">
              <div style="flex-shrink: 0; width: 30px; height: 30px; border-radius: 50%; background: rgba(<?= $log->status_type === 'delivery' ? '115,103,240' : ($log->status_type === 'production' ? '245,158,11' : '163,164,204') ?>,0.15); display: flex; align-items: center; justify-content: center; color: <?= $typeColor ?>; font-size: 0.75rem;">
                <i class="fa-solid <?= $typeIcon ?>"></i>
              </div>
              <div style="flex: 1; min-width: 0;">
                <div style="font-size: 0.82rem; color: #e4e6f4; line-height: 1.4;">
                  <?= e($log->notes ?: ($log->from_status . ' → ' . $log->to_status)) ?>
                </div>
                <div style="font-size: 0.73rem; color: #6c7293; margin-top: 4px; display: flex; gap: 8px;">
                  <span><?= format_datetime($log->created_at) ?></span>
                  <?php if ($log->changed_by_name): ?>
                    <span>• <?= e($log->changed_by_name) ?></span>
                  <?php endif; ?>
                  <span style="color: <?= $typeColor ?>; opacity: 0.8;">• <?= ucfirst($log->status_type) ?></span>
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
function previewProof(input) {
  const preview = document.getElementById('proof-preview');
  const img = document.getElementById('proof-img');
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = function(e) {
      img.src = e.target.result;
      preview.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
