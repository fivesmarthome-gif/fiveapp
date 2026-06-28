<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa thông tin nhân viên</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/staff/{$user->id}/edit") ?>" method="POST">
      <?= csrf_field() ?>

      <div class="form-group">
        <label class="form-label">Số điện thoại đăng nhập (Không thể sửa)</label>
        <input type="text" class="form-control" value="<?= e($user->phone) ?>" disabled style="opacity: 0.6;">
      </div>

      <div class="form-group">
        <label class="form-label" for="new_password">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
        <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Nhập mật khẩu mới ít nhất 6 ký tự">
      </div>

      <div class="form-group">
        <label class="form-label" for="name">Họ và tên nhân viên<span class="required">*</span></label>
        <input type="text" name="name" id="name" class="form-control" value="<?= e($user->name) ?>" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Địa chỉ Email</label>
        <input type="email" name="email" id="email" class="form-control" value="<?= e($user->email) ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="branch_id">Chi nhánh làm việc</label>
        <select name="branch_id" id="branch_id" class="form-control">
          <option value="">-- Chọn chi nhánh --</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b->id ?>" <?= $b->id == $user->branch_id ? 'selected' : '' ?>><?= e($b->name) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <!-- Steps Assignments -->
      <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; margin-bottom: 20px;">
        <h3 class="text-sm font-semibold text-white mb-3">Phân công các khâu sản xuất chính</h3>
        <div class="grid grid-2">
          <?php foreach ($steps as $num => $step): ?>
            <label class="form-check mb-2">
              <input type="checkbox" name="steps[]" value="<?= e($step['name']) ?>" <?= in_array($step['name'], $assignedStepNames) ? 'checked' : '' ?>>
              <span class="form-check-label"><?= $num ?>. <?= e($step['name']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="flex justify-end gap-3 mt-6">
        <a href="<?= url("/admin/staff/{$user->id}") ?>" class="btn btn-outline">Huỷ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Cập nhật nhân viên</button>
      </div>
    </form>
  </div>
</div>
