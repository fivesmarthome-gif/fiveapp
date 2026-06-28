<div class="card animate-fade-in">
  <div class="card-header">
    <span class="card-title">Chỉnh sửa bài viết</span>
  </div>
  <div class="card-body">
    <form action="<?= url("/admin/articles/{$article->id}/edit") ?>" method="POST" enctype="multipart/form-data" id="form-article">
      <?= csrf_field() ?>

      <div class="flex" style="gap: 24px; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 300px;">
          <div class="form-group">
            <label class="form-label" for="title">Tiêu đề bài viết<span class="required">*</span></label>
            <input type="text" name="title" id="title" class="form-control <?= has_error('title') ? 'is-invalid' : '' ?>" value="<?= old('title', $article->title) ?>" required>
            <?= error('title') ?>
          </div>

          <div class="form-group">
            <label class="form-label" for="summary">Tóm tắt ngắn</label>
            <textarea name="summary" id="summary" class="form-control" rows="3"><?= old('summary', $article->summary) ?></textarea>
          </div>

          <div class="form-group">
            <label class="form-label" for="content">Nội dung bài viết<span class="required">*</span></label>
            <textarea name="content" id="content" class="form-control <?= has_error('content') ? 'is-invalid' : '' ?>" rows="15" required><?= old('content', $article->content) ?></textarea>
            <?= error('content') ?>
          </div>
        </div>

        <div style="width: 300px;">
          <div class="form-group">
            <label class="form-label" for="category_id">Danh mục</label>
            <select name="category_id" id="category_id" class="form-control">
              <option value="">-- Chọn danh mục --</option>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat->id ?>" <?= old('category_id', $article->category_id) == $cat->id ? 'selected' : '' ?>><?= e($cat->name) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="status">Trạng thái</label>
            <select name="status" id="status" class="form-control">
              <option value="draft" <?= old('status', $article->status) == 'draft' ? 'selected' : '' ?>>Bản nháp</option>
              <option value="published" <?= old('status', $article->status) == 'published' ? 'selected' : '' ?>>Xuất bản ngay</option>
              <option value="hidden" <?= old('status', $article->status) == 'hidden' ? 'selected' : '' ?>>Đã ẩn</option>
            </select>
          </div>

          <div class="form-group">
            <label class="form-label" for="image">Ảnh đại diện</label>
            <input type="file" name="image" id="image" class="form-control" accept="image/*" onchange="previewImage(this, 'image-preview')">
            <div class="text-xs text-muted mt-1">Bỏ trống nếu không muốn thay đổi ảnh</div>
            <?php $hasImage = !empty($article->image); ?>
            <div class="mt-2" id="image-preview" style="display: <?= $hasImage ? 'block' : 'none' ?>; width: 100%; height: 150px; border-radius: 6px; background-size: cover; background-position: center; border: 1px dashed #2f3349; <?= $hasImage ? 'background-image: url(' . url('/' . ltrim($article->image, '/')) . ')' : '' ?>"></div>
          </div>

          <div class="mt-6">
            <button type="submit" class="btn btn-primary w-full"><i class="fa-solid fa-save"></i> Cập nhật bài viết</button>
            <a href="<?= url('/admin/articles') ?>" class="btn btn-outline w-full mt-2" style="text-align: center;">Huỷ</a>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
  function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      reader.onload = function(e) {
        preview.style.display = 'block';
        preview.style.backgroundImage = 'url(' + e.target.result + ')';
      }
      reader.readAsDataURL(input.files[0]);
    }
  }

  document.getElementById('form-article').addEventListener('submit', function(e) {
    if (!validateForm('form-article')) {
      e.preventDefault();
    }
  });
</script>
