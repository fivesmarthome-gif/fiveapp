<div class="card mb-6">
  <div class="card-body">
    <form action="<?= url('/admin/orders') ?>" method="GET" class="flex flex-wrap gap-3">
      <div class="search-box" style="flex: 2; min-width: 200px;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Tìm theo mã đơn, khách hàng, nha khoa..." value="<?= e($search) ?>">
      </div>

      <div style="flex: 1; min-width: 150px;">
        <select name="status" class="form-control">
          <option value="">-- Tất cả trạng thái --</option>
          <option value="new" <?= $filterStatus === 'new' ? 'selected' : '' ?>>Mới</option>
          <option value="processing" <?= $filterStatus === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
          <option value="completed" <?= $filterStatus === 'completed' ? 'selected' : '' ?>>Hoàn thành</option>
          <option value="cancelled" <?= $filterStatus === 'cancelled' ? 'selected' : '' ?>>Đã huỷ</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 150px;">
        <select name="priority" class="form-control">
          <option value="">-- Tất cả độ ưu tiên --</option>
          <option value="normal" <?= $filterPriority === 'normal' ? 'selected' : '' ?>>Thường</option>
          <option value="urgent" <?= $filterPriority === 'urgent' ? 'selected' : '' ?>>Gấp</option>
          <option value="emergency" <?= $filterPriority === 'emergency' ? 'selected' : '' ?>>Khẩn cấp</option>
        </select>
      </div>

      <div style="flex: 1; min-width: 120px; display:flex; gap: 8px;">
        <button type="submit" class="btn btn-primary flex-1"><i class="fa-solid fa-filter"></i> Lọc</button>
        <a href="<?= url('/admin/orders') ?>" class="btn btn-outline" title="Reset lọc"><i class="fa-solid fa-rotate-left"></i></a>
      </div>
    </form>
  </div>
</div>

<div class="section-header">
  <h2 class="section-title">Danh sách đơn hàng</h2>
  <a href="<?= url('/admin/orders/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Tạo đơn hàng mới</a>
</div>

<div class="card">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Mã đơn</th>
          <th>Phòng khám & Bác sĩ</th>
          <th>Ngày nhận</th>
          <th>Hẹn trả</th>
          <th>Độ ưu tiên</th>
          <th>Sản xuất</th>
          <th>Giao hàng</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($orders)): ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-6">Không tìm thấy đơn hàng nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($orders as $order): ?>
            <tr>
              <td><span class="font-bold text-white"><?= e($order->order_code) ?></span></td>
              <td>
                <div class="font-medium text-white"><?= e($order->clinic_name) ?></div>
                <div class="text-xs text-secondary"><?= e($order->customer_name) ?> (<?= e($order->customer_phone) ?>)</div>
              </td>
              <td><?= format_date($order->received_date) ?></td>
              <td><span class="<?= due_date_class($order->due_date) ?>"><?= format_date($order->due_date) ?></span></td>
              <td><?= priority_badge($order->priority) ?></td>
              <td><?= status_badge($order->production_status, 'production') ?></td>
              <td><?= status_badge($order->delivery_status, 'delivery') ?></td>
              <td><?= status_badge($order->overall_status, 'overall') ?></td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/orders/{$order->id}") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                  <a href="<?= url("/admin/orders/{$order->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;" title="Sửa"><i class="fa-solid fa-pencil"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
  
  <!-- Pagination -->
  <?php if ($pagination['last_page'] > 1): ?>
    <div class="card-footer">
      <div class="pagination">
        <?php if ($pagination['current_page'] > 1): ?>
          <a href="<?= url('/admin/orders?page=' . ($pagination['current_page'] - 1) . ($search ? '&search=' . urlencode($search) : '') . ($filterStatus ? '&status=' . $filterStatus : '') . ($filterPriority ? '&priority=' . $filterPriority : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
          <a href="<?= url('/admin/orders?page=' . $i . ($search ? '&search=' . urlencode($search) : '') . ($filterStatus ? '&status=' . $filterStatus : '') . ($filterPriority ? '&priority=' . $filterPriority : '')) ?>" class="page-btn <?= $pagination['current_page'] === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
          <a href="<?= url('/admin/orders?page=' . ($pagination['current_page'] + 1) . ($search ? '&search=' . urlencode($search) : '') . ($filterStatus ? '&status=' . $filterStatus : '') . ($filterPriority ? '&priority=' . $filterPriority : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</div>
