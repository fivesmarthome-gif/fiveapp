<div class="section-header animate-fade-in">
  <h2 class="section-title">Danh sách bài viết</h2>
  <a href="<?= url('/admin/articles/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Viết bài mới</a>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Hình ảnh</th>
          <th>Tiêu đề</th>
          <th>Danh mục</th>
          <th>Tác giả</th>
          <th>Lượt xem</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($articles)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-6">Chưa có bài viết nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($articles as $article): ?>
            <tr>
              <td>
                <?php if ($article->image): ?>
                  <img src="<?= url('/' . ltrim($article->image, '/')) ?>" alt="Image" style="width: 80px; height: 60px; object-fit: cover; border-radius: 4px;">
                <?php else: ?>
                  <div style="width: 80px; height: 60px; background: #2a2a35; border-radius: 4px; display: flex; align-items: center; justify-content: center; color: #6c7293;">
                    <i class="fa-solid fa-image"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <div style="max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($article->title) ?>">
                  <span class="font-bold text-white"><?= e($article->title) ?></span>
                </div>
                <div class="text-xs text-muted mt-1"><?= date('d/m/Y H:i', strtotime($article->created_at)) ?></div>
              </td>
              <td><?= e($article->category_name ?? 'Không có') ?></td>
              <td><?= e($article->author_name ?? 'Admin') ?></td>
              <td><i class="fa-solid fa-eye text-muted"></i> <?= number_format($article->views) ?></td>
              <td>
                <?php if ($article->status == 'published'): ?>
                  <span class="badge badge-success">Đã xuất bản</span>
                <?php elseif ($article->status == 'draft'): ?>
                  <span class="badge badge-warning">Bản nháp</span>
                <?php else: ?>
                  <span class="badge badge-danger">Đã ẩn</span>
                <?php endif; ?>
              </td>
              <td>
                <div class="flex gap-2">
                  <a href="<?= url("/admin/articles/{$article->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Chỉnh sửa"><i class="fa-solid fa-pencil"></i></a>
                  <form action="<?= url("/admin/articles/{$article->id}/delete") ?>" method="POST" style="display:inline;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
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
