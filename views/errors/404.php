<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Không tìm thấy trang | HoanKiem LAB</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
  <style>
    body {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      background-color: var(--bg);
      color: var(--text-primary);
    }
    .error-container {
      text-align: center;
      padding: 40px;
      max-width: 480px;
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
    }
    .error-code {
      font-size: 5rem;
      font-weight: 800;
      background: linear-gradient(135deg, var(--primary), var(--secondary));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      line-height: 1;
      margin-bottom: 16px;
    }
  </style>
</head>
<body>
  <div class="error-container">
    <div class="error-code">404</div>
    <h2 class="mb-2">Không tìm thấy trang</h2>
    <p class="text-secondary mb-6">Trang bạn yêu cầu không tồn tại hoặc đã bị di chuyển.</p>
    <a href="<?= url('/') ?>" class="btn btn-primary">
      <i class="fa-solid fa-house"></i> Quay lại trang chủ
    </a>
  </div>
</body>
</html>
