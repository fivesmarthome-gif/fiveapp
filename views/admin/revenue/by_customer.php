<div class="section-header animate-fade-in">
  <h2 class="section-title">Doanh thu theo khách hàng</h2>
  <a href="<?= url('/admin/revenue') ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Trở về thống kê chung</a>
</div>

<div class="card animate-fade-in animate-delay-1">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Khách hàng</th>
          <th>Phòng khám</th>
          <th>Điện thoại</th>
          <th>Tổng đơn hàng</th>
          <th>Tổng giá trị đơn</th>
          <th>Đã thanh toán</th>
          <th>Công nợ (Còn nợ)</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($customersRevenue)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-6">Chưa có dữ liệu.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($customersRevenue as $row): 
            $debt = $row->order_value - $row->paid_value;
          ?>
            <tr>
              <td><span class="font-bold text-white"><?= e($row->name) ?></span></td>
              <td><?= e($row->clinic_name) ?></td>
              <td><?= e($row->phone) ?></td>
              <td><span class="badge bg-secondary"><?= number_format($row->total_orders) ?></span></td>
              <td class="font-bold text-primary"><?= number_format($row->order_value) ?> đ</td>
              <td class="font-bold text-success"><?= number_format($row->paid_value) ?> đ</td>
              <td>
                <?php if ($debt > 0): ?>
                  <span class="font-bold text-danger"><?= number_format($debt) ?> đ</span>
                <?php elseif ($debt < 0): ?>
                  <span class="font-bold text-warning" title="Khách trả dư">Thuế <?= number_format(abs($debt)) ?> đ</span>
                <?php else: ?>
                  <span class="text-muted">0 đ</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= url("/admin/users/{$row->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;" title="Chi tiết khách hàng"><i class="fa-solid fa-user"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
