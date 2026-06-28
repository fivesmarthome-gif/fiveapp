<div class="section-header animate-fade-in">
  <h2 class="section-title">Thống kê doanh thu năm <?= e($selectedYear) ?></h2>
  <a href="<?= url('/admin/revenue/by_customer') ?>" class="btn btn-outline"><i class="fa-solid fa-users"></i> Doanh thu theo khách hàng</a>
</div>

<div class="card animate-fade-in" style="margin-bottom: 20px;">
  <div class="card-body">
    <form action="<?= url('/admin/revenue') ?>" method="GET" class="flex gap-4 align-center">
      <div class="form-group" style="margin-bottom: 0;">
        <label for="year" class="form-label" style="display: none;">Năm</label>
        <select name="year" id="year" class="form-control" style="width: 150px;">
          <?php for($y = date('Y') - 5; $y <= date('Y'); $y++): ?>
            <option value="<?= $y ?>" <?= $selectedYear == $y ? 'selected' : '' ?>>Năm <?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom: 0;">
        <label for="month" class="form-label" style="display: none;">Tháng</label>
        <select name="month" id="month" class="form-control" style="width: 150px;">
          <option value="">Tất cả các tháng</option>
          <?php for($m = 1; $m <= 12; $m++): ?>
            <option value="<?= $m ?>" <?= $selectedMonth == $m ? 'selected' : '' ?>>Tháng <?= $m ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-filter"></i> Lọc</button>
    </form>
  </div>
</div>

<div class="grid" style="grid-template-columns: 1fr 3fr; gap: 20px;">
  <div>
    <div class="card animate-fade-in animate-delay-1" style="background: linear-gradient(135deg, #7367f0 0%, #a59ef5 100%); color: white;">
      <div class="card-body" style="text-align: center; padding: 40px 20px;">
        <div style="font-size: 1.2rem; opacity: 0.9; margin-bottom: 10px;">Tổng doanh thu</div>
        <div style="font-size: 2.5rem; font-weight: 700;"><?= number_format($totalRevenue) ?> đ</div>
        <div style="margin-top: 15px; font-size: 0.9rem; opacity: 0.8;">Đã thanh toán thành công</div>
      </div>
    </div>
    
    <?php if (empty($selectedMonth) && !empty($monthlySummary)): ?>
    <div class="card animate-fade-in animate-delay-2 mt-4">
      <div class="card-header">
        <span class="card-title">Tóm tắt theo tháng</span>
      </div>
      <div class="card-body p-0">
        <table style="margin: 0; box-shadow: none;">
          <tbody>
            <?php foreach ($monthlySummary as $row): ?>
            <tr>
              <td>Tháng <?= $row->m ?></td>
              <td class="text-right font-bold text-primary"><?= number_format($row->total) ?> đ</td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <div class="card animate-fade-in animate-delay-1">
    <div class="card-header">
      <span class="card-title">Chi tiết thanh toán</span>
    </div>
    <div class="table-wrapper">
      <table>
        <thead>
          <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Phòng khám</th>
            <th>Phương thức</th>
            <th>Số tiền</th>
            <th>Ngày thanh toán</th>
            <th>Trạng thái</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($payments)): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-6">Không có dữ liệu thanh toán nào.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($payments as $payment): ?>
              <tr>
                <td><a href="<?= url("/admin/orders/{$payment->order_id}/detail") ?>" class="text-primary font-bold">#<?= e($payment->order_code) ?></a></td>
                <td><?= e($payment->customer_name ?? 'N/A') ?></td>
                <td><?= e($payment->clinic_name ?? 'N/A') ?></td>
                <td>
                  <?php 
                    $methodMap = ['cash' => 'Tiền mặt', 'bank_transfer' => 'Chuyển khoản', 'credit' => 'Công nợ'];
                    echo e($methodMap[$payment->payment_method] ?? $payment->payment_method);
                  ?>
                </td>
                <td class="font-bold text-success"><?= number_format($payment->amount) ?> đ</td>
                <td><?= date('d/m/Y H:i', strtotime($payment->paid_at)) ?></td>
                <td>
                  <?php if ($payment->status == 'confirmed'): ?>
                    <span class="badge badge-success">Thành công</span>
                  <?php elseif ($payment->status == 'pending'): ?>
                    <span class="badge badge-warning">Chờ xử lý</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Thất bại</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
