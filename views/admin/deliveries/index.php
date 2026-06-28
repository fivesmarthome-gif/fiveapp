<?php
$statusOptions = [
    ''               => 'Tất cả trạng thái',
    'waiting_pickup' => '⏳ Chờ lấy hàng',
    'shipping'       => '🚚 Đang vận chuyển',
    'delivered'      => '✅ Đã giao hàng',
    'pending_return' => '↩️ Chờ hoàn trả',
    'returned'       => '🔄 Đã hoàn trả',
];

// Count by status for quick stats
$stats = [];
foreach (array_keys($statusOptions) as $s) {
    if ($s) $stats[$s] = $db->count('deliveries', 'status = ?', [$s]);
}
?>

<!-- Quick Stats -->
<div class="grid animate-fade-in" style="grid-template-columns: repeat(5, 1fr); gap: 12px; margin-bottom: 20px;">
  <?php
  $statConfig = [
    'waiting_pickup' => ['label' => 'Chờ lấy hàng', 'icon' => 'fa-box',           'color' => '#f59e0b'],
    'shipping'       => ['label' => 'Đang giao',     'icon' => 'fa-truck-moving',   'color' => '#3b82f6'],
    'delivered'      => ['label' => 'Đã giao',       'icon' => 'fa-circle-check',   'color' => '#10b981'],
    'pending_return' => ['label' => 'Chờ hoàn trả',  'icon' => 'fa-clock-rotate-left', 'color' => '#ef4444'],
    'returned'       => ['label' => 'Hoàn trả',      'icon' => 'fa-rotate-left',    'color' => '#6366f1'],
  ];
  foreach ($statConfig as $key => $cfg):
  ?>
    <a href="<?= url('/admin/deliveries?status=' . $key) ?>" class="card" style="
      text-decoration: none; padding: 16px; cursor: pointer;
      border: 1px solid <?= $filterStatus === $key ? $cfg['color'] : 'rgba(255,255,255,0.06)' ?>;
      background: <?= $filterStatus === $key ? "rgba({$cfg['color']},0.08)" : '' ?>;
      transition: all 0.2s;
    " onmouseover="this.style.borderColor='<?= $cfg['color'] ?>'" onmouseout="this.style.borderColor='<?= $filterStatus === $key ? $cfg['color'] : 'rgba(255,255,255,0.06)' ?>'">
      <div style="display: flex; align-items: center; gap: 10px;">
        <i class="fa-solid <?= $cfg['icon'] ?>" style="color: <?= $cfg['color'] ?>; font-size: 1.1rem;"></i>
        <div>
          <div style="font-size: 1.5rem; font-weight: 700; color: white;"><?= $stats[$key] ?? 0 ?></div>
          <div style="font-size: 0.75rem; color: #a3a4cc;"><?= $cfg['label'] ?></div>
        </div>
      </div>
    </a>
  <?php endforeach; ?>
</div>

<!-- Filter bar -->
<div class="card animate-fade-in" style="margin-bottom: 20px;">
  <div class="card-body" style="padding: 12px 20px;">
    <form action="<?= url('/admin/deliveries') ?>" method="GET" class="flex gap-3 align-center">
      <select name="status" class="form-control" style="max-width: 250px;">
        <?php foreach ($statusOptions as $val => $label): ?>
          <option value="<?= $val ?>" <?= $filterStatus === $val ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
      <?php if ($filterStatus): ?>
        <a href="<?= url('/admin/deliveries') ?>" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Xoá lọc</a>
      <?php endif; ?>
    </form>
  </div>
</div>

<div class="section-header animate-fade-in">
  <h2 class="section-title">Quản lý giao nhận vận chuyển</h2>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Đơn hàng</th>
          <th>Khách hàng</th>
          <th>Phương thức</th>
          <th>Vận chuyển</th>
          <th>Shipper</th>
          <th>Thời gian</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($deliveries)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-6">Không có vận đơn nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($deliveries as $d): ?>
            <tr>
              <td>
                <a href="<?= url("/admin/orders/{$d->order_id}") ?>" class="font-bold text-white" style="text-decoration: none;"><?= e($d->order_code) ?></a>
              </td>
              <td>
                <div class="font-medium text-white"><?= e($d->clinic_name) ?></div>
                <div class="text-xs text-muted"><?= e($d->customer_name) ?></div>
              </td>
              <td>
                <?php
                $methodMap = ['internal' => ['Nội bộ LAB', '#7367f0'], 'courier' => ['Hãng ngoài', '#3b82f6'], 'pickup' => ['KH tự lấy', '#10b981']];
                $m = $methodMap[$d->method] ?? ['Nội bộ', '#6b7280'];
                ?>
                <span class="badge" style="background: <?= $m[1] ?>22; color: <?= $m[1] ?>; border: 1px solid <?= $m[1] ?>44;"><?= $m[0] ?></span>
              </td>
              <td>
                <?php if ($d->courier_name || $d->tracking_number): ?>
                  <div class="text-sm text-white"><?= e($d->courier_name ?: '—') ?></div>
                  <?php if ($d->tracking_number): ?>
                    <div class="text-xs" style="color: #7367f0; font-family: monospace;"><?= e($d->tracking_number) ?></div>
                  <?php endif; ?>
                <?php else: ?>
                  <span class="text-muted text-sm">—</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($d->shipped_at): ?>
                  <div class="text-xs text-muted">Gửi: <?= format_date($d->shipped_at) ?></div>
                <?php endif; ?>
                <?php if ($d->delivered_at): ?>
                  <div class="text-xs text-success">Giao: <?= format_date($d->delivered_at) ?></div>
                <?php else: ?>
                  <div class="text-xs <?= due_date_class($d->due_date) ?>">Hẹn: <?= format_date($d->due_date) ?></div>
                <?php endif; ?>
              </td>
              <td><?= status_badge($d->status, 'delivery') ?></td>
              <td>
                <a href="<?= url("/admin/deliveries/{$d->id}") ?>" class="btn btn-outline btn-sm" style="padding: 4px 10px;">
                  <i class="fa-solid fa-truck"></i> Cập nhật
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
