<?php
$hasLocation = $delivery->shipper_lat !== null && $delivery->shipper_lng !== null;
$mapUrl = $hasLocation ? 'https://www.google.com/maps?q=' . rawurlencode($delivery->shipper_lat . ',' . $delivery->shipper_lng) : '';
?>

<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title"><?= e($delivery->order_code) ?></span>
    <?= status_badge($delivery->status, 'delivery') ?>
  </div>
  <div class="card-body">
    <div class="font-bold text-white mb-1"><?= e($delivery->clinic_name ?: $delivery->customer_name) ?></div>
    <div class="text-sm text-muted mb-2"><i class="fa-solid fa-phone"></i> <?= e($delivery->recipient_phone ?: $delivery->customer_phone) ?></div>
    <div class="text-sm text-muted"><i class="fa-solid fa-location-dot"></i> <?= e($delivery->delivery_address ?: $delivery->customer_address ?: 'Chưa có địa chỉ') ?></div>
  </div>
</div>

<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title"><i class="fa-solid fa-location-arrow"></i> Vị trí shipper</span>
  </div>
  <div class="card-body">
    <?php if ($hasLocation): ?>
      <div style="border:1px solid rgba(59,130,246,0.3); background:rgba(59,130,246,0.08); border-radius:10px; padding:14px; margin-bottom:14px;">
        <div class="font-bold text-white mb-1"><?= e($delivery->shipper_location_note ?: 'Đã cập nhật vị trí') ?></div>
        <div class="text-xs text-muted">Tọa độ: <?= e($delivery->shipper_lat) ?>, <?= e($delivery->shipper_lng) ?></div>
        <div class="text-xs text-primary-color mt-1">Lúc: <?= format_datetime($delivery->shipper_location_updated_at) ?></div>
      </div>
      <a href="<?= e($mapUrl) ?>" target="_blank" class="btn btn-outline btn-sm mb-3" style="width:100%;">
        <i class="fa-solid fa-map-location-dot"></i> Mở bản đồ
      </a>
    <?php else: ?>
      <div class="text-sm text-muted mb-3">Chưa có vị trí. Bấm nút bên dưới để lấy GPS từ điện thoại.</div>
    <?php endif; ?>

    <?php if (in_array($delivery->status, ['waiting_pickup', 'shipping'])): ?>
      <form action="<?= url("/shipper/deliveries/{$delivery->id}/location") ?>" method="POST" id="location-form">
        <?= csrf_field() ?>
        <input type="hidden" name="shipper_lat" id="shipper_lat" value="<?= e($delivery->shipper_lat ?? '') ?>">
        <input type="hidden" name="shipper_lng" id="shipper_lng" value="<?= e($delivery->shipper_lng ?? '') ?>">
        <div class="form-group">
          <label class="form-label">Ghi chú vị trí</label>
          <input type="text" name="shipper_location_note" class="form-control" value="<?= e($delivery->shipper_location_note ?? '') ?>" placeholder="VD: Cách phòng khám 2km, đang tới nơi...">
        </div>
        <div class="flex gap-2">
          <button type="button" class="btn btn-outline" style="flex:1;" onclick="getCurrentLocation()">
            <i class="fa-solid fa-crosshairs"></i> Lấy GPS
          </button>
          <button type="submit" class="btn btn-primary" style="flex:1;">
            <i class="fa-solid fa-paper-plane"></i> Cập nhật
          </button>
        </div>
        <div id="gps-status" class="text-xs text-muted mt-2"></div>
      </form>
    <?php endif; ?>
  </div>
</div>

<?php if ($delivery->status === 'shipping'): ?>
  <form action="<?= url("/shipper/deliveries/{$delivery->id}/delivered") ?>" method="POST" class="mb-4">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-success" style="width:100%;">
      <i class="fa-solid fa-circle-check"></i> Xác nhận đã giao hàng
    </button>
  </form>
<?php endif; ?>

<div class="card animate-fade-in">
  <div class="card-header"><span class="card-title"><i class="fa-solid fa-timeline"></i> Lịch sử giao hàng</span></div>
  <div class="card-body p-0">
    <?php if (empty($logs)): ?>
      <div class="text-center text-muted py-4">Chưa có lịch sử.</div>
    <?php else: ?>
      <div class="divide-y">
        <?php foreach ($logs as $log): ?>
          <div class="p-3">
            <div class="text-sm text-white"><?= e($log->notes ?: ($log->from_status . ' -> ' . $log->to_status)) ?></div>
            <div class="text-xs text-muted"><?= format_datetime($log->created_at) ?><?= $log->changed_by_name ? ' - ' . e($log->changed_by_name) : '' ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
function getCurrentLocation() {
  var status = document.getElementById('gps-status');
  status.textContent = 'Đang lấy vị trí GPS...';

  if (!navigator.geolocation) {
    status.textContent = 'Thiết bị không hỗ trợ GPS. Vui lòng nhập bằng trình duyệt khác.';
    return;
  }

  navigator.geolocation.getCurrentPosition(function(pos) {
    document.getElementById('shipper_lat').value = pos.coords.latitude.toFixed(7);
    document.getElementById('shipper_lng').value = pos.coords.longitude.toFixed(7);
    status.textContent = 'Đã lấy vị trí: ' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5);
  }, function(error) {
    status.textContent = 'Không lấy được GPS: ' + error.message;
  }, {
    enableHighAccuracy: true,
    timeout: 12000,
    maximumAge: 30000
  });
}
</script>
