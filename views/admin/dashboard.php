<!-- Overview statistics cards -->
<div class="grid grid-4 mb-6">
  <div class="stat-card" style="--stat-color: var(--primary); --stat-rgb: 99,102,241;">
    <div class="stat-icon"><i class="fa-solid fa-file-invoice"></i></div>
    <div class="stat-value"><?= $stats['new_orders'] ?></div>
    <div class="stat-label">Đơn hàng mới</div>
  </div>

  <div class="stat-card" style="--stat-color: var(--warning); --stat-rgb: 245,158,11;">
    <div class="stat-icon"><i class="fa-solid fa-gears"></i></div>
    <div class="stat-value"><?= $stats['in_production'] ?></div>
    <div class="stat-label">Đang sản xuất</div>
  </div>

  <div class="stat-card" style="--stat-color: var(--success); --stat-rgb: 16,185,129;">
    <div class="stat-icon"><i class="fa-solid fa-truck-ramp-box"></i></div>
    <div class="stat-value"><?= $stats['ready_to_ship'] ?></div>
    <div class="stat-label">Chờ giao hàng</div>
  </div>

  <div class="stat-card" style="--stat-color: var(--accent); --stat-rgb: 6,184,212;">
    <div class="stat-icon"><i class="fa-solid fa-coins"></i></div>
    <div class="stat-value"><?= format_money($revenueMonth) ?></div>
    <div class="stat-label">Doanh thu tháng này</div>
  </div>
</div>

<div class="grid grid-3 mb-6">
  <!-- Alerts Column -->
  <div class="card flex-col" style="grid-column: span 1;">
    <div class="card-header"><span class="card-title">Cảnh báo hệ thống</span></div>
    <div class="card-body">
      <?php if ($stats['overdue'] > 0): ?>
        <div class="alert alert-danger mb-3" style="padding: 10px;">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <div>Có <a href="<?= url('/admin/orders?status=processing') ?>" class="font-bold"><?= $stats['overdue'] ?> đơn trễ hạn</a> trả!</div>
        </div>
      <?php endif; ?>

      <?php if ($stats['low_stock'] > 0): ?>
        <div class="alert alert-warning mb-3" style="padding: 10px;">
          <i class="fa-solid fa-boxes-stacked"></i>
          <div>Có <a href="<?= url('/admin/materials') ?>" class="font-bold"><?= $stats['low_stock'] ?> vật liệu</a> sắp hết kho!</div>
        </div>
      <?php endif; ?>

      <?php if ($stats['pending_feedback'] > 0): ?>
        <div class="alert alert-info mb-3" style="padding: 10px;">
          <i class="fa-solid fa-comments"></i>
          <div>Có <a href="<?= url('/admin/feedbacks') ?>" class="font-bold"><?= $stats['pending_feedback'] ?> phản hồi</a> chưa trả lời!</div>
        </div>
      <?php endif; ?>

      <?php if ($stats['overdue'] == 0 && $stats['low_stock'] == 0 && $stats['pending_feedback'] == 0): ?>
        <div class="text-center text-muted py-6">
          <i class="fa-solid fa-shield-cat" style="font-size: 2.5rem; opacity:0.3; margin-bottom:12px;"></i>
          <p>Hệ thống hoạt động ổn định</p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Chart Column -->
  <div class="card flex-col" style="grid-column: span 2;">
    <div class="card-header">
      <span class="card-title">Doanh thu 7 ngày gần đây</span>
    </div>
    <div class="card-body">
      <div style="height: 180px; display:flex; align-items:flex-end; gap: 16px; padding-top: 10px;">
        <?php
        $maxRev = 0;
        foreach ($chartData as $d) { if ($d['revenue'] > $maxRev) $maxRev = $d['revenue']; }
        if ($maxRev == 0) $maxRev = 1;
        ?>
        <?php foreach ($chartData as $d): ?>
          <?php $percent = ($d['revenue'] / $maxRev) * 100; ?>
          <div style="flex:1; display:flex; flex-direction:column; align-items:center; height:100%; justify-content:flex-end;">
            <div style="font-size: 0.72rem; margin-bottom:4px; font-weight:600; color:var(--primary-light);">
              <?= $d['revenue'] > 0 ? format_number($d['revenue'] / 1000) . 'k' : '' ?>
            </div>
            <div style="width:100%; max-width:32px; height: <?= max(5, $percent) ?>%; background: linear-gradient(180deg, var(--primary), var(--secondary)); border-radius: 4px 4px 0 0;" title="<?= format_money($d['revenue']) ?>"></div>
            <div style="font-size: 0.72rem; color:var(--text-muted); margin-top:8px;"><?= $d['date'] ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</div>

<div class="grid grid-3">
  <!-- Recent Orders -->
  <div class="card flex-col" style="grid-column: span 2;">
    <div class="card-header">
      <span class="card-title">Đơn hàng mới nhận</span>
      <a href="<?= url('/admin/orders') ?>" class="btn btn-outline btn-sm">Xem tất cả</a>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Hẹn trả</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recentOrders as $order): ?>
            <tr>
              <td><span class="font-bold text-white"><?= e($order->order_code) ?></span></td>
              <td>
                <div class="font-medium"><?= e($order->customer_name) ?></div>
                <div class="text-xs text-muted"><?= e($order->clinic_name) ?></div>
              </td>
              <td><span class="<?= due_date_class($order->due_date) ?>"><?= format_date($order->due_date) ?></span></td>
              <td><?= status_badge($order->overall_status, 'overall') ?></td>
              <td>
                <a href="<?= url("/admin/orders/{$order->id}") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;">Xem</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Appointments list -->
  <div class="card flex-col" style="grid-column: span 1;">
    <div class="card-header"><span class="card-title">Lịch hẹn hôm nay</span></div>
    <div class="card-body p-0">
      <?php if (empty($todayAppointments)): ?>
        <div class="empty-state">
          <i class="fa-solid fa-calendar-day"></i>
          <h3>Không có lịch hẹn nào</h3>
          <p>Không có cuộc hẹn gặp khách hàng nào trong ngày hôm nay.</p>
        </div>
      <?php else: ?>
        <div class="divide-y">
          <?php foreach ($todayAppointments as $app): ?>
            <div class="p-3">
              <div class="flex justify-between items-start mb-1">
                <span class="text-sm font-bold text-white"><?= date('H:i', strtotime($app->appointment_date)) ?></span>
                <span class="badge badge-purple"><?= e($app->type) ?></span>
              </div>
              <div class="text-sm"><?= e($app->customer_name) ?></div>
              <div class="text-xs text-secondary mt-1">Phụ trách: <?= e($app->staff_name ?: 'Chưa phân công') ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
