<div class="card mb-6">
  <div class="card-body">
    <form action="<?= url('/admin/staff') ?>" method="GET" class="flex gap-3">
      <div class="search-box" style="flex: 1;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Tìm theo tên nhân viên, số điện thoại..." value="<?= e($search) ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Tìm kiếm</button>
      <a href="<?= url('/admin/staff') ?>" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
    </form>
  </div>
</div>

<div class="section-header">
  <h2 class="section-title">Danh sách nhân viên kỹ thuật</h2>
  <a href="<?= url('/admin/staff/create') ?>" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Thêm nhân viên mới</a>
</div>

<div class="card animate-fade-in">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Họ và tên nhân viên</th>
          <th>Chi nhánh làm việc</th>
          <th>Số điện thoại</th>
          <th>Email liên hệ</th>
          <th>Công đoạn phụ trách chính</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($users)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-6">Không tìm thấy nhân viên nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($users as $user): ?>
            <tr>
              <td>
                <div class="flex items-center gap-3">
                  <img src="<?= avatar_url($user->avatar) ?>" alt="Avatar" class="rounded-full" style="width: 32px; height: 32px; object-fit: cover;">
                  <span class="font-bold text-white"><?= e($user->name) ?></span>
                </div>
              </td>
              <td><?= e($user->branch_name ?: 'Chưa sắp xếp') ?></td>
              <td><?= e($user->phone) ?></td>
              <td><?= e($user->email) ?></td>
              <td>
                <?php
                $assignments = $db->fetchAll("SELECT step_name FROM production_assignments WHERE user_id = ?", [$user->id]);
                $stepList = array_column($assignments, 'step_name');
                if (empty($stepList)) {
                    echo '<span class="text-muted">Chưa phân công</span>';
                } else {
                    echo implode(', ', array_map('e', $stepList));
                }
                ?>
              </td>
              <td>
                <span class="badge <?= $user->is_active ? 'badge-success' : 'badge-danger' ?>">
                  <?= $user->is_active ? 'Đang hoạt động' : 'Đã nghỉ việc' ?>
                </span>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/staff/{$user->id}") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
                  <a href="<?= url("/admin/staff/{$user->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding:4px 8px;" title="Sửa"><i class="fa-solid fa-pencil"></i></a>
                  <form action="<?= url("/admin/staff/{$user->id}/toggle") ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn thay đổi trạng thái nhân viên này?')">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm <?= $user->is_active ? 'text-danger' : 'text-success' ?>" style="padding:4px 8px;" title="<?= $user->is_active ? 'Khoá' : 'Kích hoạt' ?>">
                      <i class="fa-solid fa-<?= $user->is_active ? 'user-slash' : 'user-check' ?>"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
