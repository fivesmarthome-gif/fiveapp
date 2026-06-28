<div class="grid grid-3 mb-6 animate-fade-in">
  <!-- Main Info Card -->
  <div class="card flex-col" style="grid-column: span 2;">
    <div class="card-header">
      <span class="card-title">Chi tiết đơn hàng: <?= e($order->order_code) ?></span>
      <div>
        <span class="mr-2">Trạng thái:</span>
        <?= status_badge($order->overall_status, 'overall') ?>
      </div>
    </div>
    <div class="card-body">
      <div class="grid grid-2 mb-4">
        <div>
          <div class="text-xs text-muted mb-1">Khách hàng / Nha khoa:</div>
          <div class="font-bold text-white text-base"><?= e($order->clinic_name) ?></div>
          <div class="text-sm text-secondary"><?= e($order->customer_name) ?> (<?= e($order->customer_phone) ?>)</div>
          <div class="text-xs text-muted mt-1">Đ/C: <?= e($order->customer_address) ?></div>
        </div>
        <div>
          <div class="text-xs text-muted mb-1">Thời gian:</div>
          <div class="text-sm">Nhận đơn: <strong><?= format_date($order->received_date) ?></strong></div>
          <div class="text-sm">Hẹn trả hàng: <strong class="<?= due_date_class($order->due_date) ?>"><?= format_date($order->due_date) ?></strong></div>
          <?php if ($order->adjusted_due_date): ?>
            <div class="text-sm text-warning">Khách chỉnh ngày: <strong><?= format_date($order->adjusted_due_date) ?></strong></div>
          <?php endif; ?>
          <div class="text-xs text-muted mt-2">Mã liên kết (due-date): 
            <a href="<?= url("/order/change-due-date/{$order->due_date_token}") ?>" target="_blank" class="text-primary-color"><?= e($order->due_date_token) ?></a>
          </div>
        </div>
      </div>

      <div class="border-top pt-4 mb-4">
        <h3 class="text-sm font-semibold text-white mb-3">Sản phẩm phục hình chỉ định</h3>
        <div class="table-wrapper border">
          <table>
            <thead>
              <tr>
                <th>Sản phẩm</th>
                <th>Số răng</th>
                <th>Màu sắc (Shade)</th>
                <th>Vật liệu</th>
                <th>Số lượng</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($items as $item): ?>
                <tr>
                  <td><span class="font-bold text-white"><?= e($item->product_name) ?></span></td>
                  <td><?= e($item->tooth_numbers) ?></td>
                  <td><?= e($item->shade) ?></td>
                  <td><?= e($item->material_type) ?></td>
                  <td><?= $item->quantity ?></td>
                  <td><?= format_money($item->unit_price) ?></td>
                  <td><span class="font-medium text-white"><?= format_money($item->amount) ?></span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($order->notes): ?>
        <div class="border-top pt-3">
          <div class="text-xs text-muted mb-1">Ghi chú bác sĩ:</div>
          <p class="text-sm bg-hover p-3 rounded" style="border: 1px solid var(--border);"><?= e($order->notes) ?></p>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Financial Summaries & Actions -->
  <div class="card flex-col" style="grid-column: span 1;">
    <div class="card-header"><span class="card-title">Thanh toán & Thao tác</span></div>
    <div class="card-body">
      <div class="bg-hover p-4 rounded-lg border mb-4">
        <div class="flex justify-between text-sm mb-2">
          <span>Tổng tiền hàng:</span>
          <span><?= format_money($order->total_amount) ?></span>
        </div>
        <div class="flex justify-between text-sm mb-2 text-danger">
          <span>Khấu trừ (Giảm giá):</span>
          <span>-<?= format_money($order->discount) ?></span>
        </div>
        <div class="flex justify-between text-sm mb-2 border-top pt-2 font-bold text-white">
          <span>Thành tiền phải thu:</span>
          <span><?= format_money($order->total_amount - $order->discount) ?></span>
        </div>
        <div class="flex justify-between text-sm mb-2 text-success font-bold">
          <span>Đã thanh toán:</span>
          <span><?= format_money($order->paid_amount) ?></span>
        </div>
        <div class="flex justify-between text-sm border-top pt-2 font-bold text-warning">
          <span>Còn nợ:</span>
          <span><?= format_money(max(0, ($order->total_amount - $order->discount) - $order->paid_amount)) ?></span>
        </div>
      </div>

      <!-- Action Panel -->
      <div class="flex flex-col gap-2">
        <?php if ($order->production_status === 'pending'): ?>
          <form action="<?= url("/admin/orders/{$order->id}/confirm") ?>" method="POST">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-primary btn-block"><i class="fa-solid fa-clipboard-check"></i> Xác nhận đơn & Sản xuất</button>
          </form>
        <?php endif; ?>

        <?php if ($order->delivery_status === 'waiting_pickup'): ?>
          <button onclick="openModal('modal-delivery-ship')" class="btn btn-warning btn-block"><i class="fa-solid fa-truck"></i> Giao cho đơn vị vận chuyển</button>
        <?php endif; ?>

        <?php if ($order->delivery_status === 'pending_return'): ?>
          <form action="<?= url("/admin/orders/{$order->id}/approve-return") ?>" method="POST">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger btn-block"><i class="fa-solid fa-undo"></i> Chấp nhận yêu cầu hoàn trả</button>
          </form>
        <?php endif; ?>

        <?php if ($order->overall_status !== 'completed' && $order->overall_status !== 'cancelled'): ?>
          <button onclick="openModal('modal-payment')" class="btn btn-success btn-block"><i class="fa-solid fa-dollar-sign"></i> Thu tiền thanh toán</button>
          <button onclick="openModal('modal-cancel')" class="btn btn-outline btn-block text-danger"><i class="fa-solid fa-ban"></i> Huỷ đơn hàng</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Dynamic Rework / Step Tracker -->
<?php if (!empty($steps)): ?>
  <div class="card mb-6 animate-fade-in">
    <div class="card-header"><span class="card-title">Tiến độ chi tiết 8 công đoạn sản xuất</span></div>
    <div class="card-body">
      <div class="table-wrapper">
        <table>
          <thead>
            <tr>
              <th>Khâu sản xuất</th>
              <th>Nhân viên phụ trách</th>
              <th>Trạng thái</th>
              <th>Bắt đầu</th>
              <th>Hoàn thành</th>
              <th>Ghi chú / Rework</th>
              <th>Thao tác</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($steps as $step): ?>
              <tr>
                <td><span class="font-bold text-white"><?= e($step->step_name) ?></span></td>
                <td>
                  <?php if ($step->assigned_to): ?>
                    <span class="font-medium text-white"><?= e($step->staff_name) ?></span>
                  <?php else: ?>
                    <span class="text-muted">Chưa giao</span>
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
                  <button onclick="openAssignModal(<?= $step->id ?>, <?= $step->assigned_to ?: 'null' ?>)" class="btn btn-outline btn-sm" style="padding: 2px 6px;">Giao việc</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

<!-- Payment Modal -->
<div class="modal-overlay" id="modal-payment">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Thu tiền thanh toán đơn hàng</span>
      <button class="btn-icon" onclick="closeModal('modal-payment')">×</button>
    </div>
    <form action="<?= url('/admin/payments/create') ?>" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="order_id" value="<?= $order->id ?>">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Số tiền cần thu (đ)<span class="required">*</span></label>
          <input type="number" name="amount" class="form-control" value="<?= max(0, ($order->total_amount - $order->discount) - $order->paid_amount) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phương thức thanh toán</label>
          <select name="method" class="form-control">
            <option value="transfer">Chuyển khoản ngân hàng</option>
            <option value="cash">Tiền mặt</option>
            <option value="card">Thẻ / POS</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú giao dịch</label>
          <textarea name="notes" class="form-control" placeholder="Mã giao dịch ngân hàng..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-payment')">Huỷ</button>
        <button type="submit" class="btn btn-success">Ghi nhận thu tiền</button>
      </div>
    </form>
  </div>
</div>

<!-- Cancel Order Modal -->
<div class="modal-overlay" id="modal-cancel">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title text-danger">Xác nhận huỷ đơn hàng</span>
      <button class="btn-icon" onclick="closeModal('modal-cancel')">×</button>
    </div>
    <form action="<?= url("/admin/orders/{$order->id}/cancel") ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Lý do huỷ đơn hàng này<span class="required">*</span></label>
          <textarea name="cancel_reason" class="form-control" placeholder="Bác sĩ hủy ngang, chọn sai thông tin răng..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-cancel')">Huỷ</button>
        <button type="submit" class="btn btn-danger">Xác nhận huỷ đơn</button>
      </div>
    </form>
  </div>
</div>

<!-- Delivery Ship Modal -->
<div class="modal-overlay" id="modal-delivery-ship">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Bàn giao vận chuyển</span>
      <button class="btn-icon" onclick="closeModal('modal-delivery-ship')">×</button>
    </div>
    <form action="<?= url("/admin/orders/{$order->id}/delivery-status") ?>" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="delivery_status" value="shipping">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Đơn vị vận chuyển</label>
          <input type="text" name="courier_name" class="form-control" placeholder="GHTK, Viettel Post, Grab..." required>
        </div>
        <div class="form-group">
          <label class="form-label">Mã vận đơn / Tracking code</label>
          <input type="text" name="tracking_number" class="form-control" placeholder="Nhập mã vận đơn nếu có">
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú giao nhận</label>
          <textarea name="notes" class="form-control" placeholder="Tên và SĐT shipper..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-delivery-ship')">Huỷ</button>
        <button type="submit" class="btn btn-warning">Gửi hàng đi</button>
      </div>
    </form>
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
          <label class="form-label">Chọn nhân viên thực hiện</label>
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
