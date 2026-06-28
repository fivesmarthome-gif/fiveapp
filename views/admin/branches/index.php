<div class="section-header animate-fade-in">
  <h2 class="section-title">Danh sách chi nhánh</h2>
  <a href="<?= url('/admin/branches/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Thêm chi nhánh mới</a>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Tên chi nhánh</th>
          <th>Địa chỉ</th>
          <th>Điện thoại</th>
          <th>Hotline</th>
          <th>Giờ làm việc</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($branches)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-6">Chưa có dữ liệu chi nhánh.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($branches as $branch): ?>
            <tr>
              <td><span class="font-bold text-white"><?= e($branch->name) ?></span></td>
              <td><?= e($branch->address) ?></td>
              <td><?= e($branch->phone) ?></td>
              <td><?= e($branch->hotline) ?></td>
              <td><?= e($branch->working_hours) ?></td>
              <td>
                <?php if ($branch->is_active): ?>
                  <span class="badge badge-success">Hoạt động</span>
                <?php else: ?>
                  <span class="badge badge-danger">Tạm ngưng</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/branches/{$branch->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
