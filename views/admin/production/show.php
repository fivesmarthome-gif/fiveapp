<div class="card mb-6 animate-fade-in">
  <div class="card-header">
    <span class="card-title">Quản lý chi tiết công đoạn đơn hàng: <?= e($order->order_code) ?></span>
    <?= status_badge($order->production_status, 'production') ?>
  </div>
  <div class="card-body">
    <div class="table-wrapper border">
      <table>
        <thead>
          <tr>
            <th>Chỉ định răng</th>
            <th>Khâu sản xuất</th>
            <th>Nhân viên phụ trách</th>
            <th>Trạng thái</th>
            <th>Bắt đầu làm</th>
            <th>Hoàn thành</th>
            <th>Ghi chú / Lý do làm lại</th>
            <th>Thao tác</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($steps as $step): ?>
            <tr>
              <td>
                <div class="font-bold text-white"><?= e($step->product_name) ?></div>
                <div class="text-xs text-secondary">Răng: <?= e($step->tooth_numbers) ?></div>
              </td>
              <td><span class="font-bold text-white"><?= $step->step_number ?>. <?= e($step->step_name) ?></span></td>
              <td>
                <?php if ($step->assigned_to): ?>
                  <span class="font-medium text-white"><?= e($step->staff_name) ?></span>
                <?php else: ?>
                  <span class="text-muted">Chưa phân công</span>
                <?php endif; ?>
              </td>
              <td>
                <span class="badge <?= $step->status === 'completed' ? 'badge-success' : ($step->status === 'in_progress' ? 'badge-warning' : ($step->status === 'rework' ? 'badge-danger' : 'badge-muted')) ?>">
                  <?= $step->status === 'completed' ? 'Hoàn thành' : ($step->status === 'in_progress' ? 'Đang làm' : ($step->status === 'rework' ? 'Yêu cầu làm lại' : 'Chờ')) ?>
                </span>
              </td>
              <td><?= $step->started_at ? format_datetime($step->started_at) : '-' ?></td>
              <td><?= $step->completed_at ? format_datetime($step->completed_at) : '-' ?></td>
              <td>
                <?php if ($step->status === 'rework'): ?>
                  <span class="text-danger font-medium"><?= e($step->rework_reason) ?></span>
                <?php else: ?>
                  <?= e($step->notes) ?>
                <?php endif; ?>
              </td>
              <td>
                <div class="flex gap-2">
                  <button onclick="openAssignModal(<?= $step->id ?>, <?= $step->assigned_to ?: 'null' ?>)" class="btn btn-outline btn-sm" style="padding: 2px 6px;">Phân công</button>
                  <?php if ($step->step_number === 8 && $step->status === 'completed' && $order->production_status !== 'qc_passed' && $order->production_status !== 'ready'): ?>
                    <form action="<?= url("/admin/production/{$step->id}/qc-pass") ?>" method="POST" style="display:inline;">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-success btn-sm" style="padding: 2px 6px;">Duyệt QC đạt</button>
                    </form>
                  <?php endif; ?>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Assign Step Modal -->
<div class="modal-overlay" id="modal-assign-step">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Phân công kỹ thuật viên</span>
      <button class="btn-icon" onclick="closeModal('modal-assign-step')">×</button>
    </div>
    <form action="" method="POST" id="assign-step-form">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Chọn nhân viên phụ trách khâu</label>
          <select name="staff_id" id="assign-staff-select" class="form-control" required>
            <option value="">-- Chưa giao việc --</option>
            <?php foreach ($staffList as $s): ?>
              <option value="<?= $s->id ?>"><?= e($s->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-assign-step')">Huỷ</button>
        <button type="submit" class="btn btn-primary">Xác nhận giao việc</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openAssignModal(stepId, currentStaffId) {
    const form = document.getElementById('assign-step-form');
    form.action = baseUrl + '/admin/production/' + stepId + '/assign';
    
    const select = document.getElementById('assign-staff-select');
    select.value = currentStaffId || '';
    
    openModal('modal-assign-step');
  }
</script>
