<div class="grid grid-3 mb-6 animate-fade-in">
  <div class="card flex-col" style="grid-column: span 1;">
    <div class="card-header">
      <span class="card-title">Hồ sơ khách hàng</span>
    </div>
    <div class="card-body text-center">
      <img src="<?= avatar_url($user->avatar) ?>" alt="Avatar" class="rounded-full mb-3" style="width: 100px; height: 100px; object-fit: cover; display:inline-block; border: 2px solid var(--border);">
      <h3 class="font-bold text-white text-lg"><?= e($user->name) ?></h3>
      <p class="text-xs text-muted mb-4"><?= e($user->phone) ?></p>

      <div class="text-left divide-y">
        <div class="py-2 flex justify-between">
          <span class="text-secondary text-sm">Phòng khám:</span>
          <span class="font-medium text-white text-sm"><?= e($user->clinic_name) ?></span>
        </div>
        <div class="py-2 flex justify-between">
          <span class="text-secondary text-sm">Mã số thuế:</span>
          <span class="text-sm"><?= e($user->tax_code) ?></span>
        </div>
        <div class="py-2 flex justify-between">
          <span class="text-secondary text-sm">Doanh số:</span>
          <span class="font-bold text-success text-sm"><?= format_money($totalRevenue) ?></span>
        </div>
        <div class="py-2 flex justify-between">
          <span class="text-secondary text-sm">Công nợ hiện tại:</span>
          <span class="font-bold text-danger text-sm"><?= format_money($user->balance) ?></span>
        </div>
        <div class="py-2">
          <span class="text-secondary text-xs block mb-1">Địa chỉ giao nhận hàng:</span>
          <span class="text-xs text-white"><?= e($user->address) ?></span>
        </div>
      </div>

      <div class="mt-6 flex flex-col gap-2">
        <a href="<?= url("/admin/customers/{$user->id}/edit") ?>" class="btn btn-primary btn-block"><i class="fa-solid fa-pencil"></i> Chỉnh sửa thông tin</a>
        <a href="<?= url('/admin/customers') ?>" class="btn btn-outline btn-block">Quay lại danh sách</a>
      </div>
    </div>
  </div>

  <div class="card flex-col" style="grid-column: span 2;">
    <div class="card-header">
      <span class="card-title">Lịch sử đặt hàng gần đây</span>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Hẹn trả</th>
            <th>Sản xuất</th>
            <th>Giao nhận</th>
            <th>Tổng tiền</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($orders)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-6">Chưa có đơn hàng nào từ khách hàng này.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($orders as $order): ?>
              <tr>
                <td><span class="font-bold text-white"><?= e($order->order_code) ?></span></td>
                <td><span class="<?= due_date_class($order->due_date) ?>"><?= format_date($order->due_date) ?></span></td>
                <td><?= status_badge($order->production_status, 'production') ?></td>
                <td><?= status_badge($order->delivery_status, 'delivery') ?></td>
                <td><?= format_money($order->total_amount - $order->discount) ?></td>
                <td><?= status_badge($order->overall_status, 'overall') ?></td>
                <td>
                  <a href="<?= url("/admin/orders/{$order->id}") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;"><i class="fa-solid fa-eye"></i></a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
