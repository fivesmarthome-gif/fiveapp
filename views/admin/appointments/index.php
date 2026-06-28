<div class="section-header">
  <h2 class="section-title">Danh sách lịch hẹn khách hàng</h2>
  <a href="<?= url('/admin/appointments/create') ?>" class="btn btn-primary"><i class="fa-solid fa-calendar-plus"></i> Đặt lịch hẹn mới</a>
</div>

<div class="card animate-fade-in">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Thời gian lịch hẹn</th>
          <th>Khách hàng</th>
          <th>Phòng khám</th>
          <th>Kỹ thuật viên phụ trách</th>
          <th>Loại lịch hẹn</th>
          <th>Ghi chú</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($appointments)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-6">Không có lịch hẹn nào được thiết lập.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($appointments as $app): ?>
            <tr>
              <td><span class="font-bold text-white"><?= format_datetime($app->appointment_date) ?></span></td>
              <td><span class="font-bold text-white"><?= e($app->customer_name) ?></span></td>
              <td><?= e($app->clinic_name) ?></td>
              <td><?= e($app->staff_name ?: 'Chưa phân công') ?></td>
              <td><span class="badge badge-purple"><?= e($app->type) ?></span></td>
              <td><?= e($app->notes) ?></td>
              <td>
                <span class="badge <?= $app->status === 'completed' ? 'badge-success' : ($app->status === 'cancelled' ? 'badge-danger' : 'badge-warning') ?>">
                  <?= $app->status === 'completed' ? 'Đã hoàn thành' : ($app->status === 'cancelled' ? 'Đã huỷ' : 'Đang hẹn') ?>
                </span>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/appointments/{$app->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;"><i class="fa-solid fa-pencil"></i></a>
                  <?php if ($app->status !== 'cancelled' && $app->status !== 'completed'): ?>
                    <form action="<?= url("/admin/appointments/{$app->id}/delete") ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn huỷ lịch hẹn này?')">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-outline btn-sm text-danger" style="padding: 4px 8px;"><i class="fa-solid fa-calendar-xmark"></i></button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
