<div class="section-header animate-fade-in">
  <h2 class="section-title">Danh sách loại sản phẩm</h2>
  <a href="<?= url('/admin/product-types/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Thêm loại sản phẩm</a>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Sắp xếp</th>
          <th>Tên loại sản phẩm</th>
          <th>Phân loại chung</th>
          <th>Thời gian (Ngày)</th>
          <th>Giá cơ bản</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($productTypes)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-6">Chưa có loại sản phẩm nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($productTypes as $type): ?>
            <tr>
              <td><span class="badge bg-secondary"><?= $type->sort_order ?></span></td>
              <td><span class="font-bold text-white"><?= e($type->name) ?></span></td>
              <td><?= e($type->category ?: 'Chưa phân loại') ?></td>
              <td><?= $type->estimated_days ?> ngày</td>
              <td class="font-bold text-primary"><?= number_format($type->base_price) ?> đ</td>
              <td>
                <?php if ($type->is_active): ?>
                  <span class="badge badge-success">Đang dùng</span>
                <?php else: ?>
                  <span class="badge badge-danger">Tạm ngưng</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= url("/admin/product-types/{$type->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
