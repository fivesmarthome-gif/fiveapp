<div class="section-header animate-fade-in">
  <h2 class="section-title">Chi tiết phản hồi đơn hàng #<?= e($feedback->order_code) ?></h2>
  <a href="<?= url('/admin/feedbacks') ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Quay lại</a>
</div>

<div class="grid" style="grid-template-columns: 2fr 1fr; gap: 20px;">
  <div>
    <div class="card animate-fade-in">
      <div class="card-header">
        <span class="card-title">Nội dung phản hồi</span>
      </div>
      <div class="card-body">
        <div style="display: flex; align-items: flex-start; gap: 16px;">
          <div style="width: 50px; height: 50px; border-radius: 50%; background-color: #2f3349; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #a59ef5;">
            <i class="fa-solid fa-user"></i>
          </div>
          <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div class="font-bold text-white text-lg"><?= e($feedback->customer_name) ?></div>
              <div class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($feedback->created_at)) ?></div>
            </div>
            <div class="text-warning mt-1 mb-2">
              <?php for($i=1; $i<=5; $i++): ?>
                <?php if($i <= $feedback->rating): ?>
                  <i class="fa-solid fa-star"></i>
                <?php else: ?>
                  <i class="fa-regular fa-star"></i>
                <?php endif; ?>
              <?php endfor; ?>
            </div>
            <div style="background-color: rgba(47, 51, 73, 0.5); padding: 15px; border-radius: 8px; margin-top: 10px; line-height: 1.6;">
              <?= nl2br(e($feedback->content)) ?>
            </div>
          </div>
        </div>

        <?php if ($feedback->admin_reply): ?>
          <div style="display: flex; align-items: flex-start; gap: 16px; margin-top: 30px; margin-left: 40px;">
            <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(115, 103, 240, 0.1); border: 1px solid #7367f0; display: flex; align-items: center; justify-content: center; color: #7367f0;">
              <i class="fa-solid fa-headset"></i>
            </div>
            <div style="flex: 1;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="font-bold text-primary">Phản hồi từ LAB</div>
                <div class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($feedback->replied_at)) ?></div>
              </div>
              <div style="background-color: rgba(115, 103, 240, 0.1); border: 1px solid rgba(115, 103, 240, 0.2); padding: 15px; border-radius: 8px; margin-top: 10px; line-height: 1.6; color: #e4e6f4;">
                <?= nl2br(e($feedback->admin_reply)) ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$feedback->admin_reply): ?>
      <div class="card mt-4 animate-fade-in animate-delay-1">
        <div class="card-header">
          <span class="card-title">Trả lời khách hàng</span>
        </div>
        <div class="card-body">
          <form action="<?= url("/admin/feedbacks/{$feedback->id}/reply") ?>" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
              <label for="admin_reply" class="form-label">Nội dung trả lời</label>
              <textarea name="admin_reply" id="admin_reply" class="form-control" rows="4" required placeholder="Nhập nội dung trả lời khách hàng..."></textarea>
            </div>
            <div class="flex justify-end">
              <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Gửi phản hồi</button>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>

  <div>
    <div class="card animate-fade-in animate-delay-2">
      <div class="card-header">
        <span class="card-title">Thông tin tham chiếu</span>
      </div>
      <div class="card-body">
        <table class="table-borderless" style="width: 100%;">
          <tbody>
            <tr>
              <td class="text-muted pb-2" style="width: 40%;">Đơn hàng:</td>
              <td class="pb-2 font-bold"><a href="<?= url("/admin/orders/{$feedback->order_id}/detail") ?>" class="text-primary">#<?= e($feedback->order_code) ?></a></td>
            </tr>
            <tr>
              <td class="text-muted pb-2">Khách hàng:</td>
              <td class="pb-2"><?= e($feedback->customer_name) ?></td>
            </tr>
            <tr>
              <td class="text-muted pb-2">Phòng khám:</td>
              <td class="pb-2"><?= e($feedback->clinic_name) ?></td>
            </tr>
            <tr>
              <td class="text-muted pb-2">Số điện thoại:</td>
              <td class="pb-2"><?= e($feedback->customer_phone) ?></td>
            </tr>
            <tr>
              <td class="text-muted pb-2">Trạng thái xử lý:</td>
              <td class="pb-2">
                <?php if ($feedback->status == 'resolved'): ?>
                  <span class="badge badge-success">Đã hoàn tất</span>
                <?php else: ?>
                  <span class="badge badge-warning">Chờ xử lý</span>
                <?php endif; ?>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
