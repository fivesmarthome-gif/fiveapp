<div class="card animate-fade-in" style="max-width: 600px; margin: 0 auto;">
  <div class="card-header">
    <span class="card-title">Chi tiết thông tin nhân viên</span>
  </div>
  <div class="card-body text-center">
    <img src="<?= avatar_url($user->avatar) ?>" alt="Avatar" class="rounded-full mb-3" style="width: 100px; height: 100px; object-fit: cover; display:inline-block; border: 2px solid var(--border);">
    <h3 class="font-bold text-white text-lg"><?= e($user->name) ?></h3>
    <p class="text-xs text-muted mb-4"><?= e($user->phone) ?> (Kỹ thuật viên)</p>

    <div class="text-left divide-y">
      <div class="py-2 flex justify-between">
        <span class="text-secondary text-sm">Chi nhánh:</span>
        <span class="font-medium text-white text-sm"><?= e($user->branch_name ?: 'Chưa sắp xếp') ?></span>
      </div>
      <div class="py-2 flex justify-between">
        <span class="text-secondary text-sm">Email liên hệ:</span>
        <span class="text-sm"><?= e($user->email) ?></span>
      </div>
      <div class="py-2 flex justify-between">
        <span class="text-secondary text-sm">Tổng công đoạn đã xong:</span>
        <span class="font-bold text-success text-sm"><?= $completedSteps ?></span>
      </div>
      <div class="py-3">
        <span class="text-secondary text-xs block mb-2 font-semibold">Công đoạn sản xuất được giao:</span>
        <div class="flex flex-wrap gap-2">
          <?php if (empty($assignments)): ?>
            <span class="text-muted text-xs">Chưa được gán công đoạn nào</span>
          <?php else: ?>
            <?php foreach ($assignments as $a): ?>
              <span class="badge badge-primary"><?= e($a->step_name) ?></span>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="mt-6 flex gap-2 justify-end">
      <a href="<?= url('/admin/staff') ?>" class="btn btn-outline">Quay lại</a>
      <a href="<?= url("/admin/staff/{$user->id}/edit") ?>" class="btn btn-primary"><i class="fa-solid fa-pencil"></i> Chỉnh sửa nhân viên</a>
    </div>
  </div>
</div>
