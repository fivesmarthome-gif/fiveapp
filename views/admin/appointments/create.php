<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Đặt lịch hẹn mới</span>
  </div>
  <div class="card-body">
    <form action="<?= url('/admin/appointments/create') ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label" for="customer_id">Khách hàng / Nha sĩ<span class="required">*</span></label>
        <select name="customer_id" id="customer_id" class="form-control" required>
          <option value="">-- Chọn khách hàng --</option>
          <?php foreach ($customers as $c): ?>
            <option value="<?= $c->id ?>"><?= e($c->clinic_name ?: $c->name) ?> (<?= e($c->name) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="staff_id">Nhân viên phụ trách / kỹ thuật viên</label>
        <select name="staff_id" id="staff_id" class="form-control">
          <option value="">-- Chọn nhân viên --</option>
          <?php foreach ($staffList as $s): ?>
            <option value="<?= $s->id ?>"><?= e($s->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="order_id">Đơn hàng liên kết (nếu có)</label>
        <select name="order_id" id="order_id" class="form-control">
          <option value="">-- Chọn đơn hàng --</option>
          <?php foreach ($orders as $o): ?>
            <option value="<?= $o->id ?>"><?= e($o->order_code) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="appointment_date">Thời gian lịch hẹn<span class="required">*</span></label>
        <input type="datetime-local" name="appointment_date" id="appointment_date" class="form-control" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="type">Loại lịch hẹn<span class="required">*</span></label>
        <select name="type" id="type" class="form-control" required>
          <option value="consultation">Tư vấn khớp cắn / Chọn màu răng</option>
          <option value="delivery">Bàn giao sản phẩm</option>
          <option value="checkup">Khám lâm sàng / Lấy dấu mẫu thạch cao</option>
          <option value="other">Lý do khác</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label" for="notes">Ghi chú lịch hẹn</label>
        <textarea name="notes" id="notes" class="form-control" style="height: 100px;" placeholder="Yêu cầu mang theo mẫu thạch cao, thước đo..."></textarea>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url('/admin/appointments') ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Đặt lịch hẹn</button>
      </div>
    </form>
  </div>
</div>
