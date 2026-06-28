<div class="card animate-fade-in">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa đơn hàng: <?= e($order->order_code) ?></span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/orders/{$order->id}/edit") ?>" method="POST">
      <?= csrf_field() ?>

      <div class="grid grid-2 mb-4">
        <!-- Customer (Disabled) -->
        <div class="form-group">
          <label class="form-label">Khách hàng</label>
          <select class="form-control" disabled>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c->id ?>" <?= $c->id == $order->customer_id ? 'selected' : '' ?>><?= e($c->clinic_name ?: $c->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Priority -->
        <div class="form-group">
          <label class="form-label" for="priority">Độ ưu tiên</label>
          <select name="priority" id="priority" class="form-control" required>
            <option value="normal" <?= $order->priority === 'normal' ? 'selected' : '' ?>>Thường</option>
            <option value="urgent" <?= $order->priority === 'urgent' ? 'selected' : '' ?>>Gấp</option>
            <option value="emergency" <?= $order->priority === 'emergency' ? 'selected' : '' ?>>Khẩn cấp</option>
          </select>
        </div>
      </div>

      <div class="grid grid-2 mb-4">
        <!-- Due Date -->
        <div class="form-group">
          <label class="form-label" for="due_date">Ngày hẹn trả hàng</label>
          <input type="date" name="due_date" id="due_date" class="form-control" value="<?= e($order->due_date) ?>" required>
        </div>

        <!-- Discount -->
        <div class="form-group">
          <label class="form-label" for="discount">Chiết khấu / Giảm giá (đ)</label>
          <input type="number" name="discount" id="discount" class="form-control" value="<?= e($order->discount) ?>" required>
        </div>
      </div>

      <!-- Notes -->
      <div class="form-group mb-4">
        <label class="form-label" for="notes">Ghi chú chỉ định</label>
        <textarea name="notes" id="notes" class="form-control" style="height: 100px;"><?= e($order->notes) ?></textarea>
      </div>

      <div class="flex justify-end gap-3">
        <a href="<?= url("/admin/orders/{$order->id}") ?>" class="btn btn-outline">Quay lại</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật đơn hàng</button>
      </div>
    </form>
  </div>
</div>
