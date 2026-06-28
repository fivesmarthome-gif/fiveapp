<div class="card mb-6">
  <div class="card-body">
    <form action="<?= url('/admin/production') ?>" method="GET" class="flex gap-3">
      <div style="flex: 1;">
        <select name="status" class="form-control">
          <option value="">-- Tất cả trạng thái sản xuất --</option>
          <option value="confirmed" <?= $filterStatus === 'confirmed' ? 'selected' : '' ?>>Đã xác nhận (Chưa làm)</option>
          <option value="in_production" <?= $filterStatus === 'in_production' ? 'selected' : '' ?>>Đang sản xuất</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
      <a href="<?= url('/admin/production') ?>" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
    </form>
  </div>
</div>

<div class="section-header">
  <h2 class="section-title">Theo dõi tiến độ sản xuất</h2>
</div>

<div class="card animate-fade-in">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Mã đơn hàng</th>
          <th>Phòng khám & Bác sĩ</th>
          <th>Hẹn trả</th>
          <th>Độ ưu tiên</th>
          <th>Quy trình</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-6">Không có đơn hàng nào đang sản xuất.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <?php
            $progressPercent = $order->total_steps > 0 ? (int)round(($order->completed_steps / $order->total_steps) * 100) : 0;
            ?>
            <tr>
              <td><span class="font-bold text-white"><?= e($order->order_code) ?></span></td>
              <td>
                <div class="font-medium text-white"><?= e($order->clinic_name) ?></div>
                <div class="text-xs text-secondary"><?= e($order->customer_name) ?></div>
              </td>
              <td><span class="<?= due_date_class($order->due_date) ?>"><?= format_date($order->due_date) ?></span></td>
              <td><?= priority_badge($order->priority) ?></td>
              <td>
                <div class="flex items-center gap-3">
                  <div class="progress" style="width: 100px;">
                    <div class="progress-bar" style="width: <?= $progressPercent ?>%"></div>
                  </div>
                  <span class="text-xs font-semibold text-white"><?= $progressPercent ?>%</span>
                </div>
              </td>
              <td><?= status_badge($order->production_status, 'production') ?></td>
              <td>
                <a href="<?= url("/admin/production/{$order->id}") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;"><i class="fa-solid fa-gears"></i> Quản lý khâu</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
