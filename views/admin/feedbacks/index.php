<div class="section-header animate-fade-in">
  <h2 class="section-title">Danh sách phản hồi từ khách hàng</h2>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Mã đơn hàng</th>
          <th>Khách hàng</th>
          <th>Phòng khám</th>
          <th>Đánh giá</th>
          <th>Nội dung</th>
          <th>Ngày gửi</th>
          <th>Trạng thái</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($feedbacks)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-6">Chưa có phản hồi nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($feedbacks as $feedback): ?>
            <tr>
              <td><a href="<?= url("/admin/orders/{$feedback->order_id}/detail") ?>" class="text-primary font-bold">#<?= e($feedback->order_code) ?></a></td>
              <td><?= e($feedback->customer_name) ?></td>
              <td><?= e($feedback->clinic_name) ?></td>
              <td>
                <div class="text-warning">
                  <?php for($i=1; $i<=5; $i++): ?>
                    <?php if($i <= $feedback->rating): ?>
                      <i class="fa-solid fa-star"></i>
                    <?php else: ?>
                      <i class="fa-regular fa-star"></i>
                    <?php endif; ?>
                  <?php endfor; ?>
                </div>
              </td>
              <td>
                <div style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= e($feedback->content) ?>">
                  <?= e($feedback->content) ?>
                </div>
              </td>
              <td><?= date('d/m/Y H:i', strtotime($feedback->created_at)) ?></td>
              <td>
                <?php if ($feedback->status == 'resolved'): ?>
                  <span class="badge badge-success">Đã phản hồi</span>
                <?php else: ?>
                  <span class="badge badge-warning">Chưa phản hồi</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= url("/admin/feedbacks/{$feedback->id}") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Xem chi tiết"><i class="fa-solid fa-eye"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
