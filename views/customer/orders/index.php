<!-- Tabs or Status filter -->
<div class="mb-4 overflow-auto" style="white-space: nowrap; -webkit-overflow-scrolling: touch; padding-bottom: 4px;">
  <a href="<?= url('/customer/orders') ?>" class="btn <?= empty($filterStatus) ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Tất cả</a>
  <a href="<?= url('/customer/orders?status=waiting_pickup') ?>" class="btn <?= $filterStatus === 'waiting_pickup' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Chờ lấy hàng</a>
  <a href="<?= url('/customer/orders?status=shipping') ?>" class="btn <?= $filterStatus === 'shipping' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Đang vận chuyển</a>
  <a href="<?= url('/customer/orders?status=delivered') ?>" class="btn <?= $filterStatus === 'delivered' ? 'btn-primary' : 'btn-outline' ?> btn-sm" style="margin-right: 8px;">Đã giao</a>
  <a href="<?= url('/customer/orders?status=completed') ?>" class="btn <?= $filterStatus === 'completed' ? 'btn-primary' : 'btn-outline' ?> btn-sm">Hoàn thành</a>
</div>

<div class="animate-fade-in">
  <?php if (empty($orders)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-clipboard-question"></i>
      <h3>Không tìm thấy đơn hàng</h3>
      <p>Bạn không có đơn hàng nào thuộc trạng thái này.</p>
    </div>
  <?php else: ?>
    <?php foreach ($orders as $order): ?>
      <?php
      $progress = order_progress($order->id);
      $cStep = current_step($order->id);
      ?>
      <div class="order-card">
        <div class="order-card-header">
          <div>
            <div class="order-code"><?= e($order->order_code) ?></div>
            <div class="order-date">Đặt ngày: <?= format_date($order->created_at) ?></div>
          </div>
          <?= status_badge($order->delivery_status, 'delivery') ?>
        </div>

        <div style="font-size: 0.82rem; color: var(--text-secondary); margin-bottom: 8px;">
          Hẹn trả hàng: <strong class="text-primary-color"><?= format_date($order->due_date) ?></strong>
        </div>

        <div class="order-progress-info">
          <span>Tiến độ sản xuất</span>
          <span><?= $progress ?>%</span>
        </div>
        <div class="progress mb-3">
          <div class="progress-bar" style="width: <?= $progress ?>%"></div>
        </div>

        <div class="flex justify-between items-center">
          <span class="text-xs text-muted">
            Tổng tiền: <strong class="text-white"><?= format_money($order->total_amount - $order->discount) ?></strong>
          </span>
          <a href="<?= url("/customer/orders/{$order->id}") ?>" class="btn btn-outline btn-sm">Chi tiết</a>
        </div>
      </div>
    <?php endforeach; ?>

    <!-- Pagination -->
    <?php if ($pagination['last_page'] > 1): ?>
      <div class="pagination">
        <?php if ($pagination['current_page'] > 1): ?>
          <a href="<?= url('/customer/orders?page=' . ($pagination['current_page'] - 1) . ($filterStatus ? '&status=' . $filterStatus : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-left"></i></a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $pagination['last_page']; $i++): ?>
          <a href="<?= url('/customer/orders?page=' . $i . ($filterStatus ? '&status=' . $filterStatus : '')) ?>" class="page-btn <?= $pagination['current_page'] === $i ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($pagination['current_page'] < $pagination['last_page']): ?>
          <a href="<?= url('/customer/orders?page=' . ($pagination['current_page'] + 1) . ($filterStatus ? '&status=' . $filterStatus : '')) ?>" class="page-btn"><i class="fa-solid fa-chevron-right"></i></a>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  <?php endif; ?>
</div>
