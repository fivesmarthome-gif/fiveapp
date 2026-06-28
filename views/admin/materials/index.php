<div class="card mb-6">
  <div class="card-body">
    <form action="<?= url('/admin/materials') ?>" method="GET" class="flex gap-3">
      <div class="search-box" style="flex: 1;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" name="search" class="form-control" placeholder="Tìm theo mã vật liệu, tên vật liệu..." value="<?= e($search) ?>">
      </div>
      <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Tìm kiếm</button>
      <a href="<?= url('/admin/materials') ?>" class="btn btn-outline"><i class="fa-solid fa-rotate-left"></i> Reset</a>
    </form>
  </div>
</div>

<div class="section-header">
  <h2 class="section-title">Vật liệu & Kho hàng</h2>
  <div class="flex gap-2">
    <button onclick="openModal('modal-transaction-import')" class="btn btn-success"><i class="fa-solid fa-arrow-down"></i> Nhập kho</button>
    <button onclick="openModal('modal-transaction-export')" class="btn btn-danger"><i class="fa-solid fa-arrow-up"></i> Xuất kho</button>
    <a href="<?= url('/admin/materials/create') ?>" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Thêm vật liệu mới</a>
  </div>
</div>

<div class="card animate-fade-in">
  <div class="table-wrapper">
    <table>
      <thead>
        <tr>
          <th>Mã VL</th>
          <th>Tên vật liệu</th>
          <th>Danh mục</th>
          <th>Đơn vị tính</th>
          <th>Tồn kho hiện tại</th>
          <th>Tồn kho tối thiểu</th>
          <th>Giá vốn</th>
          <th>Nhà cung cấp</th>
          <th>Thao tác</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($materials)): ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-6">Không tìm thấy vật liệu nào.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($materials as $m): ?>
            <tr style="<?= $m->current_stock <= $m->min_stock ? 'background: rgba(239,68,68,0.05);' : '' ?>">
              <td><span class="font-bold text-white"><?= e($m->code) ?></span></td>
              <td>
                <div class="font-bold text-white"><?= e($m->name) ?></div>
                <?php if ($m->current_stock <= $m->min_stock): ?>
                  <span class="text-xs text-danger font-semibold"><i class="fa-solid fa-circle-exclamation"></i> Sắp hết hàng</span>
                <?php endif; ?>
              </td>
              <td><?= e($m->category) ?></td>
              <td><?= e($m->unit) ?></td>
              <td>
                <span class="font-bold text-lg <?= $m->current_stock <= $m->min_stock ? 'text-danger' : 'text-success' ?>">
                  <?= format_number($m->current_stock) ?>
                </span>
              </td>
              <td><?= format_number($m->min_stock) ?></td>
              <td><?= format_money($m->unit_cost) ?></td>
              <td><?= e($m->supplier_name ?: 'Chưa nhập') ?></td>
              <td>
                <a href="<?= url("/admin/materials/{$m->id}/edit") ?>" class="btn btn-outline btn-sm" style="padding: 4px 8px;"><i class="fa-solid fa-pencil"></i></a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Import stock modal -->
<div class="modal-overlay" id="modal-transaction-import">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Nhập kho vật liệu</span>
      <button class="btn-icon" onclick="closeModal('modal-transaction-import')">×</button>
    </div>
    <form action="" method="POST" id="import-form">
      <?= csrf_field() ?>
      <input type="hidden" name="type" value="import">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Chọn vật liệu nhập</label>
          <select name="material_id" class="form-control" required onchange="updateFormAction(this, 'import-form')">
            <option value="">-- Chọn vật liệu --</option>
            <?php foreach ($materials as $m): ?>
              <option value="<?= $m->id ?>"><?= e($m->name) ?> (<?= e($m->code) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Số lượng nhập</label>
          <input type="number" name="quantity" class="form-control" step="any" required>
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú nhập kho</label>
          <textarea name="notes" class="form-control" placeholder="Nhập từ nhà cung cấp..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-transaction-import')">Huỷ</button>
        <button type="submit" class="btn btn-success">Xác nhận nhập kho</button>
      </div>
    </form>
  </div>
</div>

<!-- Export stock modal -->
<div class="modal-overlay" id="modal-transaction-export">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Xuất kho vật liệu</span>
      <button class="btn-icon" onclick="closeModal('modal-transaction-export')">×</button>
    </div>
    <form action="" method="POST" id="export-form">
      <?= csrf_field() ?>
      <input type="hidden" name="type" value="export">
      <div class="modal-body">
        <div class="form-group">
          <label class="form-label">Chọn vật liệu xuất</label>
          <select name="material_id" class="form-control" required onchange="updateFormAction(this, 'export-form')">
            <option value="">-- Chọn vật liệu --</option>
            <?php foreach ($materials as $m): ?>
              <option value="<?= $m->id ?>"><?= e($m->name) ?> (Tồn: <?= format_number($m->current_stock) ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Số lượng xuất</label>
          <input type="number" name="quantity" class="form-control" step="any" required>
        </div>
        <div class="form-group">
          <label class="form-label">Ghi chú xuất kho</label>
          <textarea name="notes" class="form-control" placeholder="Mục đích sử dụng, công đoạn sản xuất..."></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline" onclick="closeModal('modal-transaction-export')">Huỷ</button>
        <button type="submit" class="btn btn-danger">Xác nhận xuất kho</button>
      </div>
    </form>
  </div>
</div>

<script>
  function updateFormAction(select, formId) {
    const form = document.getElementById(formId);
    const materialId = select.value;
    if (materialId) {
      form.action = baseUrl + '/admin/materials/' + materialId + '/transaction';
    } else {
      form.action = '';
    }
  }
</script>
