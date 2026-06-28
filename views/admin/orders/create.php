<div class="card animate-fade-in">
  <div class="card-header">
    <span class="card-title">Điền thông tin đơn hàng mới</span>
  </div>
  <div class="card-body">
    <form action="<?= url('/admin/orders/create') ?>" method="POST" id="create-order-form">
      <?= csrf_field() ?>

      <div class="grid grid-2 mb-4">
        <!-- Customer Selection -->
        <div class="form-group">
          <label class="form-label" for="customer_id">Khách hàng (Nha sĩ / Phòng khám)<span class="required">*</span></label>
          <select name="customer_id" id="customer_id" class="form-control" required>
            <option value="">-- Chọn khách hàng --</option>
            <?php foreach ($customers as $c): ?>
              <option value="<?= $c->id ?>"><?= e($c->clinic_name ?: $c->name) ?> (<?= e($c->name) ?> - <?= e($c->phone) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Branch Selection -->
        <div class="form-group">
          <label class="form-label" for="branch_id">Chi nhánh nhận sản xuất</label>
          <select name="branch_id" id="branch_id" class="form-control">
            <option value="">-- Chọn chi nhánh --</option>
            <?php foreach ($branches as $b): ?>
              <option value="<?= $b->id ?>"><?= e($b->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="grid grid-3 mb-4">
        <!-- Received Date -->
        <div class="form-group">
          <label class="form-label" for="received_date">Ngày nhận đơn<span class="required">*</span></label>
          <input type="date" name="received_date" id="received_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>

        <!-- Due Date -->
        <div class="form-group">
          <label class="form-label" for="due_date">Ngày hẹn trả hàng<span class="required">*</span></label>
          <input type="date" name="due_date" id="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+5 days')) ?>" required>
        </div>

        <!-- Priority -->
        <div class="form-group">
          <label class="form-label" for="priority">Độ ưu tiên<span class="required">*</span></label>
          <select name="priority" id="priority" class="form-control" required>
            <option value="normal">Thường</option>
            <option value="urgent">Gấp (Ưu tiên)</option>
            <option value="emergency">Khẩn cấp (Làm ngay)</option>
          </select>
        </div>
      </div>

      <!-- Order Items (Dynamic List) -->
      <div style="border: 1px solid var(--border); border-radius: var(--radius); padding: 16px; margin-bottom: 20px;">
        <h3 class="text-sm font-semibold text-white mb-4">Chi tiết các chỉ định phục hình</h3>
        
        <div id="order-items-container">
          <div class="order-item-row grid grid-4 mb-3" style="align-items: flex-end;">
            <div class="form-group mb-0">
              <label class="form-label text-xs">Loại sản phẩm (Răng)</label>
              <select name="product_type_id[]" class="form-control text-sm product-select" required onchange="updatePrice(this)">
                <option value="">-- Chọn loại --</option>
                <?php foreach ($productTypes as $pt): ?>
                  <option value="<?= $pt->id ?>" data-price="<?= $pt->base_price ?>"><?= e($pt->name) ?> (<?= format_number($pt->base_price) ?>đ)</option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group mb-0">
              <label class="form-label text-xs">Số răng (chỉ định)</label>
              <input type="text" name="tooth_numbers[]" class="form-control text-sm" placeholder="Ví dụ: 11, 12, 21">
            </div>

            <div class="form-group mb-0">
              <label class="form-label text-xs">Màu sắc (Shade)</label>
              <select name="shade[]" class="form-control text-sm">
                <option value="">Chọn màu</option>
                <?php foreach ($shades as $s): ?>
                  <option value="<?= $s ?>"><?= $s ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="form-group mb-0" style="display:flex; gap: 8px;">
              <div style="flex:1;">
                <label class="form-label text-xs">Đơn giá</label>
                <input type="number" name="unit_price[]" class="form-control text-sm unit-price" placeholder="Đơn giá">
              </div>
              <div style="width: 70px;">
                <label class="form-label text-xs">SL</label>
                <input type="number" name="quantity[]" class="form-control text-sm qty-input" value="1" min="1" oninput="calculateTotal()">
              </div>
            </div>
          </div>
        </div>

        <button type="button" class="btn btn-outline btn-sm mt-3" onclick="addItemRow()"><i class="fa-solid fa-plus"></i> Thêm phục hình khác</button>
      </div>

      <div class="grid grid-2 mb-4">
        <!-- Notes -->
        <div class="form-group">
          <label class="form-label" for="notes">Ghi chú chỉ định của bác sĩ</label>
          <textarea name="notes" id="notes" class="form-control" style="height: 100px;" placeholder="Lưu ý về khớp cắn, đường hoàn tất, mẫu sáp..."></textarea>
        </div>
        
        <!-- Discount and totals summary -->
        <div class="form-group bg-hover p-4 rounded-lg border">
          <div class="flex justify-between items-center mb-3">
            <span class="text-sm">Giảm giá (đ):</span>
            <input type="number" name="discount" id="discount" class="form-control text-sm text-right" style="width: 150px;" value="0" oninput="calculateTotal()">
          </div>
          <div class="flex justify-between items-center border-top pt-3">
            <span class="font-bold text-white text-base">TỔNG THANH TOÁN:</span>
            <span class="font-bold text-success text-lg" id="overall-total">0 ₫</span>
          </div>
        </div>
      </div>

      <div class="flex justify-end gap-3">
        <a href="<?= url('/admin/orders') ?>" class="btn btn-outline">Huỷ bỏ</a>
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Tạo đơn hàng</button>
      </div>
    </form>
  </div>
</div>

<script>
  function updatePrice(select) {
    const row = select.closest('.order-item-row');
    const priceInput = row.querySelector('.unit-price');
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.dataset.price || 0;
    priceInput.value = price;
    calculateTotal();
  }

  function addItemRow() {
    const container = document.getElementById('order-items-container');
    const firstRow = container.querySelector('.order-item-row');
    const clone = firstRow.cloneNode(true);
    
    // Reset values in cloned row
    clone.querySelector('.product-select').selectedIndex = 0;
    clone.querySelector('input[name="tooth_numbers[]"]').value = '';
    clone.querySelector('select[name="shade[]"]').selectedIndex = 0;
    clone.querySelector('.unit-price').value = '';
    clone.querySelector('.qty-input').value = 1;
    
    // Add remove button
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-outline btn-sm text-danger';
    removeBtn.style.padding = '10px';
    removeBtn.style.marginTop = '6px';
    removeBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
    removeBtn.onclick = function() {
      clone.remove();
      calculateTotal();
    };
    clone.appendChild(removeBtn);

    container.appendChild(clone);
  }

  function calculateTotal() {
    let total = 0;
    const rows = document.querySelectorAll('.order-item-row');
    rows.forEach(row => {
      const price = parseFloat(row.querySelector('.unit-price').value) || 0;
      const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
      total += price * qty;
    });

    const discount = parseFloat(document.getElementById('discount').value) || 0;
    const grandTotal = Math.max(0, total - discount);
    
    document.getElementById('overall-total').innerText = formatMoney(grandTotal);
  }

  document.addEventListener('DOMContentLoaded', calculateTotal);
</script>
