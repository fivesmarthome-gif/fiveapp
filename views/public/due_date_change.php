<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle ?? 'Đổi ngày trả hàng') ?> | HoanKiem LAB</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', 'Segoe UI', sans-serif;
      background: linear-gradient(135deg, #0f0f17 0%, #1a1a2e 50%, #16213e 100%);
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }

    .card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      border-radius: 20px;
      padding: 40px;
      width: 100%;
      max-width: 520px;
      box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }

    .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 32px;
      padding-bottom: 24px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .brand-icon {
      width: 50px; height: 50px;
      background: linear-gradient(135deg, #7367f0, #a59ef5);
      border-radius: 12px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem;
    }

    .brand-name { font-size: 1.2rem; font-weight: 700; color: white; }
    .brand-sub { font-size: 0.8rem; color: #a3a4cc; }

    h2 { font-size: 1.3rem; color: white; font-weight: 600; margin-bottom: 8px; }
    .subtitle { color: #a3a4cc; font-size: 0.9rem; margin-bottom: 28px; line-height: 1.5; }

    .info-box {
      background: rgba(115, 103, 240, 0.1);
      border: 1px solid rgba(115, 103, 240, 0.25);
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 24px;
    }

    .info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 0.875rem;
      padding: 4px 0;
    }

    .info-label { color: #a3a4cc; }
    .info-value { color: white; font-weight: 600; }

    .form-group { margin-bottom: 20px; }

    label {
      display: block;
      font-size: 0.875rem;
      color: #c5c7dd;
      margin-bottom: 8px;
      font-weight: 500;
    }

    label .required { color: #f87171; margin-left: 2px; }

    input[type="date"], textarea {
      width: 100%;
      padding: 12px 16px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 8px;
      color: white;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }

    input[type="date"]:focus, textarea:focus {
      border-color: #7367f0;
      box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.2);
    }

    textarea { resize: vertical; min-height: 80px; }

    .btn {
      width: 100%;
      padding: 13px;
      border-radius: 10px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      border: none;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary {
      background: linear-gradient(135deg, #7367f0, #9c6df5);
      color: white;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(115, 103, 240, 0.4);
    }

    .alert {
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 20px;
      display: flex;
      align-items: flex-start;
      gap: 10px;
      font-size: 0.9rem;
    }

    .alert-success {
      background: rgba(40, 199, 111, 0.15);
      border: 1px solid rgba(40, 199, 111, 0.3);
      color: #28c76f;
    }

    .alert-danger {
      background: rgba(248, 113, 113, 0.15);
      border: 1px solid rgba(248, 113, 113, 0.3);
      color: #f87171;
    }

    .footer-note {
      text-align: center;
      margin-top: 24px;
      color: #6c7293;
      font-size: 0.8rem;
    }

    @media (max-width: 480px) {
      .card { padding: 24px; }
    }
  </style>
</head>
<body>

<div class="card">
  <div class="brand">
    <div class="brand-icon">🦷</div>
    <div>
      <div class="brand-name">HoanKiem LAB</div>
      <div class="brand-sub">Yêu cầu đổi ngày giao hàng</div>
    </div>
  </div>

  <?php $success = get_flash('success'); if ($success): ?>
    <div class="alert alert-success">
      <i class="fa-solid fa-circle-check"></i>
      <div><?= e($success) ?></div>
    </div>
  <?php endif; ?>

  <?php $error = get_flash('error'); if ($error): ?>
    <div class="alert alert-danger">
      <i class="fa-solid fa-circle-exclamation"></i>
      <div><?= e($error) ?></div>
    </div>
  <?php endif; ?>

  <h2>Đề nghị thay đổi ngày nhận hàng</h2>
  <p class="subtitle">
    Quý phòng khám có thể điều chỉnh ngày nhận sản phẩm theo lịch phù hợp. 
    LAB sẽ được thông báo và xem xét yêu cầu của quý vị.
  </p>

  <div class="info-box">
    <div class="info-row">
      <span class="info-label">Mã đơn hàng:</span>
      <span class="info-value">#<?= e($order->order_code) ?></span>
    </div>
    <div class="info-row">
      <span class="info-label">Ngày dự kiến giao:</span>
      <span class="info-value"><?= format_date($order->due_date) ?></span>
    </div>
    <?php if ($order->adjusted_due_date): ?>
    <div class="info-row">
      <span class="info-label">Ngày đã điều chỉnh:</span>
      <span class="info-value" style="color: #f5a623;"><?= format_date($order->adjusted_due_date) ?></span>
    </div>
    <?php endif; ?>
  </div>

  <form action="<?= url("/order/change-due-date/{$token}") ?>" method="POST">
    <?= csrf_field() ?>

    <div class="form-group">
      <label for="adjusted_due_date">Ngày nhận hàng mong muốn<span class="required">*</span></label>
      <input
        type="date"
        name="adjusted_due_date"
        id="adjusted_due_date"
        required
        min="<?= date('Y-m-d') ?>"
        value="<?= $order->adjusted_due_date ? date('Y-m-d', strtotime($order->adjusted_due_date)) : date('Y-m-d', strtotime($order->due_date)) ?>"
      >
    </div>

    <div class="form-group">
      <label for="reason">Lý do điều chỉnh (Tùy chọn)</label>
      <textarea name="reason" id="reason" placeholder="Ví dụ: Lịch phẫu thuật bị thay đổi sang ngày khác..."></textarea>
    </div>

    <button type="submit" class="btn btn-primary">
      <i class="fa-solid fa-calendar-check"></i>
      Gửi yêu cầu thay đổi ngày
    </button>
  </form>

  <div class="footer-note">
    Liên hệ hỗ trợ: <strong style="color: #a59ef5;"><?= e(setting('company_phone', '0123.456.789')) ?></strong>
  </div>
</div>

</body>
</html>
