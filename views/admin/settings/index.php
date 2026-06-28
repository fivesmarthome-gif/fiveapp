<div class="section-header animate-fade-in">
  <h2 class="section-title">Cài đặt hệ thống</h2>
</div>

<div class="card animate-fade-in animate-delay-1" style="max-width: 800px;">
  <div class="card-body">
    <form action="<?= url('/admin/settings/update') ?>" method="POST" id="form-settings">
      <?= csrf_field() ?>

      <h3 style="font-size: 1.1rem; color: #e4e6f4; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">Thông tin chung</h3>

      <div class="form-group">
        <label class="form-label" for="app_name">Tên hệ thống / Công ty</label>
        <input type="text" name="app_name" id="app_name" class="form-control" value="<?= e($settings['app_name'] ?? 'HoanKiem LAB') ?>">
      </div>

      <div class="form-group">
        <label class="form-label" for="company_address">Địa chỉ trụ sở chính</label>
        <input type="text" name="company_address" id="company_address" class="form-control" value="<?= e($settings['company_address'] ?? 'Hoàn Kiếm, Hà Nội') ?>">
      </div>

      <div class="flex" style="gap: 16px;">
        <div class="form-group" style="flex: 1;">
          <label class="form-label" for="company_phone">Số điện thoại liên hệ</label>
          <input type="text" name="company_phone" id="company_phone" class="form-control" value="<?= e($settings['company_phone'] ?? '0123.456.789') ?>">
        </div>
        <div class="form-group" style="flex: 1;">
          <label class="form-label" for="company_email">Email hỗ trợ</label>
          <input type="email" name="company_email" id="company_email" class="form-control" value="<?= e($settings['company_email'] ?? 'support@hoankiemlab.com') ?>">
        </div>
      </div>

      <h3 style="font-size: 1.1rem; color: #e4e6f4; margin-bottom: 20px; margin-top: 30px; padding-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">Cấu hình nghiệp vụ</h3>

      <div class="form-group">
        <label class="form-label" for="order_prefix">Tiền tố mã đơn hàng (Ví dụ: HKL-)</label>
        <input type="text" name="order_prefix" id="order_prefix" class="form-control" value="<?= e($settings['order_prefix'] ?? 'HKL-') ?>" style="max-width: 200px;">
      </div>

      <div class="form-group">
        <label class="form-label">Chính sách bảo hành mặc định (Tháng)</label>
        <input type="number" name="default_warranty_months" class="form-control" value="<?= e($settings['default_warranty_months'] ?? '12') ?>" style="max-width: 200px;">
      </div>

      <div class="form-group">
        <label class="form-label" for="order_notice">Thông báo đặt hàng (Hiển thị cho khách lúc tạo đơn)</label>
        <textarea name="order_notice" id="order_notice" class="form-control" rows="3"><?= e($settings['order_notice'] ?? 'Vui lòng kiểm tra kỹ thông tin vật liệu và số lượng răng trước khi tạo đơn.') ?></textarea>
      </div>

      <h3 style="font-size: 1.1rem; color: #e4e6f4; margin-bottom: 20px; margin-top: 30px; padding-bottom: 10px; border-bottom: 1px solid rgba(255, 255, 255, 0.1);">Hệ thống</h3>

      <div class="form-group">
        <label class="form-label">
          <input type="checkbox" name="maintenance_mode" value="1" <?= ($settings['maintenance_mode'] ?? '0') == '1' ? 'checked' : '' ?>>
          Kích hoạt chế độ bảo trì (Chỉ Admin mới có thể truy cập hệ thống)
        </label>
      </div>

      <div class="mt-8">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Lưu cài đặt</button>
      </div>
    </form>
  </div>
</div>
