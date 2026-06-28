<div class="section-header animate-fade-in">
  <h2 class="section-title">Danh sách chương trình khuyến mãi</h2>
  <a href="<?= url('/admin/promotions/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Thêm khuyến mãi mới</a>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Tên chương trình / Mã</th>
          <th>Hình ảnh</th>
          <th>Chiết khấu</th>
          <th>Thời gian áp dụng</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($promotions)): ?>
          <tr>
            <td colspan="6" class="text-center text-muted py-6">Chưa có dữ liệu khuyến mãi.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($promotions as $promo): ?>
            <tr>
              <td>
                <div class="font-bold text-white"><?= e($promo->name) ?></div>
                <div class="text-xs text-muted mt-1"><i class="fa-solid fa-ticket"></i> <?= e($promo->code) ?></div>
              </td>
              <td>
                <?php if ($promo->image): ?>
                  <img src="<?= url('/' . ltrim($promo->image, '/')) ?>" alt="Image" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                  <div style="width: 80px; height: 50px; background: #2a2a35; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c7293;">
                    <i class="fa-solid fa-image"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <span class="text-primary font-bold">
                  <?php if ($promo->discount_type == 'percent'): ?>
                    -<?= number_format($promo->discount_value) ?>%
                  <?php else: ?>
                    -<?= number_format($promo->discount_value) ?>đ
                  <?php endif; ?>
                </span>
              </td>
              <td>
                <div class="text-sm">
                  Từ: <?= date('d/m/Y H:i', strtotime($promo->start_date)) ?><br>
                  Đến: <?= date('d/m/Y H:i', strtotime($promo->end_date)) ?>
                </div>
              </td>
              <td>
                <?php
                $now = date('Y-m-d H:i:s');
                if ($promo->start_date > $now): ?>
                  <span class="badge badge-warning">Sắp diễn ra</span>
                <?php elseif ($promo->end_date < $now): ?>
                  <span class="badge badge-danger">Đã kết thúc</span>
                <?php else: ?>
                  <span class="badge badge-success">Đang diễn ra</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/promotions/{$promo->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
                  <form action="<?= url("/admin/promotions/{$promo->id}/delete") ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này không?');">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline btn-sm" style="padding: 4px 8px; color: #f87171; border-color: #f87171;" title="Xoá"><i class="fa-solid fa-trash"></i></button>
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
