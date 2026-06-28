<div class="mb-4">
  <form action="<?= url('/staff/customers') ?>" method="GET">
    <div class="search-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" class="form-control" placeholder="Tìm kiếm khách hàng, phòng khám..." value="<?= e($search ?? '') ?>">
    </div>
  </form>
</div>

<div class="animate-fade-in">
  <?php if (empty($customers)): ?>
    <div class="empty-state">
      <i class="fa-solid fa-user-slash"></i>
      <h3>Không tìm thấy khách hàng</h3>
      <p>Thử tìm kiếm với từ khóa khác.</p>
    </div>
  <?php else: ?>
    <div class="divide-y border rounded-lg bg-card overflow-hidden">
      <?php foreach ($customers as $cust): ?>
        <a href="<?= url("/staff/customers/{$cust->id}") ?>" class="p-3 block bg-hover" style="text-decoration:none; color:inherit;">
          <div class="flex justify-between items-center">
            <div>
              <h4 class="text-sm font-semibold text-white"><?= e($cust->name) ?></h4>
              <p class="text-xs text-secondary mt-1">Nha khoa: <?= e($cust->clinic_name) ?></p>
            </div>
            <i class="fa-solid fa-chevron-right text-muted" style="font-size:0.8rem;"></i>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
