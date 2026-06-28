<div class="card mb-4 animate-fade-in">
  <div class="card-header">
    <span class="card-title">Đơn hàng: <?= e($step->order_code) ?></span>
    <?= priority_badge($step->priority) ?>
  </div>
  <div class="card-body">
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Khách hàng:</span>
      <span class="font-medium"><?= e($step->clinic_name ?: $step->customer_name) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Sản phẩm:</span>
      <span class="font-medium text-white"><?= e($step->product_name) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Công đoạn:</span>
      <span class="font-medium text-warning"><?= e($step->step_name) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Số răng:</span>
      <span class="font-medium text-white"><?= e($step->tooth_numbers) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Màu răng (Shade):</span>
      <span class="font-medium text-white"><?= e($step->shade) ?></span>
    </div>
    <div class="flex justify-between mb-2">
      <span class="text-secondary">Vật liệu:</span>
      <span><?= e($step->material_type) ?></span>
    </div>
    <div class="flex justify-between">
      <span class="text-secondary">Hạn trả hàng:</span>
      <span class="<?= due_date_class($step->due_date) ?> font-medium"><?= format_date($step->due_date) ?></span>
    </div>

    <?php if ($step->specifications): ?>
    <div class="mt-4 border-top pt-3">
      <div class="text-xs text-muted mb-1">Yêu cầu kỹ thuật:</div>
      <p class="text-sm bg-hover p-3 rounded" style="border: 1px solid var(--border);"><?= e($step->specifications) ?></p>
    </div>
    <?php endif; ?>

    <?php if ($step->order_notes): ?>
    <div class="mt-3">
      <div class="text-xs text-muted mb-1">Ghi chú đơn hàng:</div>
      <p class="text-sm"><?= e($step->order_notes) ?></p>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Rework warning if applicable -->
<?php if ($step->status === 'rework'): ?>
  <div class="alert alert-danger mb-4">
    <i class="fa-solid fa-triangle-exclamation"></i>
    <div>
      <strong>Làm lại:</strong> <?= e($step->rework_reason) ?>
    </div>
  </div>
<?php endif; ?>

<!-- Timer and Actions -->
<?php if ($step->status === 'in_progress'): ?>
  <div class="card mb-4 text-center">
    <div class="card-body">
      <div class="text-xs text-muted mb-1">Thời gian thực hiện</div>
      <div class="text-2xl font-bold mb-4" id="step-timer" style="font-family: monospace; color: var(--warning);">00:00:00</div>
      
      <form action="<?= url("/staff/production/{$step->id}/complete") ?>" method="POST" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="form-group text-left">
          <label class="form-label">Ghi chú sản xuất (nếu có)</label>
          <textarea name="notes" class="form-control" placeholder="Nhập ghi chú cho khâu sản xuất này..."></textarea>
        </div>

        <div class="form-group text-left">
          <label class="form-label">Ảnh sản phẩm sau hoàn thiện (nếu có)</label>
          <input type="file" name="step_images[]" multiple class="form-control" accept="image/*">
        </div>

        <button type="submit" class="btn btn-success btn-block"><i class="fa-solid fa-circle-check"></i> Hoàn thành công đoạn</button>
      </form>

      <button onclick="openModal('modal-rework')" class="btn btn-outline btn-block mt-3"><i class="fa-solid fa-triangle-exclamation"></i> Báo làm lại (Rework)</button>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      startStepTimer('<?= $step->started_at ?>');
    });
  </script>
<?php elseif ($step->status === 'waiting'): ?>
  <form action="<?= url("/staff/production/{$step->id}/start") ?>" method="POST" class="mb-4">
    <?= csrf_field() ?>
    <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fa-solid fa-play"></i> Bắt đầu thực hiện</button>
  </form>
<?php elseif ($step->status === 'completed'): ?>
  <div class="alert alert-success">
    <i class="fa-solid fa-circle-check"></i>
    <div>
      Đã hoàn thành lúc <?= format_datetime($step->completed_at) ?>
    </div>
  </div>
<?php endif; ?>

<!-- Modal Rework request -->
<div class="modal-overlay" id="modal-rework">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Báo lỗi / Cần làm lại</span>
      <button class="btn-icon" onclick="closeModal('modal-rework')">×</button>
    </div>
    <form action="<?= url("/staff/production/{$step->id}/rework") ?>" method="POST">
      <?= csrf_field() ?>
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Lý do yêu cầu làm lại công đoạn này</label>
          <textarea name="rework_reason" class="form-control" placeholder="Mô tả chi tiết lỗi phát hiện hoặc lý do cần mài sửa/làm lại từ khâu trước..." required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-rework')">Huỷ</button>
        <button type="submit" class="btn btn-danger">Xác nhận báo lỗi</button>
      </div>
    </form>
  </div>
</div>
