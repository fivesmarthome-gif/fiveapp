<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa thông tin lịch hẹn</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/appointments/{$appointment->id}/edit") ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Khách hàng</label>
        <select class="form-control" disabled style="opacity: 0.6;">
          <?php foreach ($customers as $c): ?>
            <option value="<?= $c->id ?>" <?= $c->id == $appointment->customer_id ? 'selected' : '' ?>><?= e($c->clinic_name ?: $c->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="staff_id">Nhân viên phụ trách / kỹ thuật viên</label>
        <select name="staff_id" id="staff_id" class="form-control">
          <option value="">-- Chọn nhân viên --</option>
          <?php foreach ($staffList as $s): ?>
            <option value="<?= $s->id ?>" <?= $s->id == $appointment->staff_id ? 'selected' : '' ?>><?= e($s->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="appointment_date">Thời gian lịch hẹn<span class="required">*</span></label>
        <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" value="<?= date('Y-m-d\TH:i', strtotime($appointment->appointment_date)) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="type">Loại lịch hẹn</label>
        <select name="type" id="type" class="form-control" required>
          <option value="consultation" <?= $appointment->type === 'consultation' ? 'selected' : '' ?>>Tư vấn khớp cắn</option>
          <option value="delivery" <?= $appointment->type === 'delivery' ? 'selected' : '' ?>>Bàn giao sản phẩm</option>
          <option value="checkup" <?= $appointment->type === 'checkup' ? 'selected' : '' ?>>Khám lâm sàng / Lấy dấu</option>
          <option value="other" <?= $appointment->type === 'other' ? 'selected' : '' ?>>Lý do khác</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="status">Trạng thái lịch hẹn</label>
        <select name="status" id="status" class="form-control" required>
          <option value="scheduled" <?= $appointment->status === 'scheduled' ? 'selected' : '' ?>>Đang lên lịch (Chờ)</option>
          <option value="completed" <?= $appointment->status === 'completed' ? 'selected' : '' ?>>Đã hoàn thành</option>
          <option value="cancelled" <?= $appointment->status === 'cancelled' ? 'selected' : '' ?>>Đã huỷ</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="notes">Ghi chú lịch hẹn</label>
        <textarea name="notes" id="notes" class="form-control" style="height: 100px;"><?= e($appointment->notes) ?></textarea>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/appointments') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật lịch hẹn</button>
      </div>
    </form>
  </div>
</div>
