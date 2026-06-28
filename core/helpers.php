<?php
/**
 * Helper Functions
 * HoanKiem LAB
 */

// ============================================
// URL Helpers
// ============================================

/**
 * Generate full URL
 */
function url(string $path = ''): string
{
    $config = require dirname(__DIR__) . '/config/app.php';
    return $config['base_url'] . '/' . ltrim($path, '/');
}

/**
 * Generate asset URL
 */
function asset(string $path): string
{
    return url('public/' . ltrim($path, '/'));
}

/**
 * Generate upload URL
 */
function upload_url(string $path): string
{
    return url($path);
}

/**
 * Check if current URL matches
 */
function is_active(string $path): bool
{
    $currentUri = $_SERVER['REQUEST_URI'] ?? '';
    $fullPath = url($path);
    return strpos($currentUri, $fullPath) !== false;
}

/**
 * Get active class
 */
function active_class(string $path, string $class = 'active'): string
{
    return is_active($path) ? $class : '';
}

// ============================================
// String Helpers
// ============================================

/**
 * Escape HTML output
 */
function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate slug from string
 */
function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    // Vietnamese characters
    $search  = ['à','á','ạ','ả','ã','â','ầ','ấ','ậ','ẩ','ẫ','ă','ằ','ắ','ặ','ẳ','ẵ','è','é','ẹ','ẻ','ẽ','ê','ề','ế','ệ','ể','ễ','ì','í','ị','ỉ','ĩ','ò','ó','ọ','ỏ','õ','ô','ồ','ố','ộ','ổ','ỗ','ơ','ờ','ớ','ợ','ở','ỡ','ù','ú','ụ','ủ','ũ','ư','ừ','ứ','ự','ử','ữ','ỳ','ý','ỵ','ỷ','ỹ','đ'];
    $replace = ['a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','a','e','e','e','e','e','e','e','e','e','e','e','i','i','i','i','i','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','o','u','u','u','u','u','u','u','u','u','u','u','y','y','y','y','y','d'];
    $text = str_replace($search, $replace, $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    return trim($text, '-');
}

/**
 * Truncate text
 */
function str_limit(string $text, int $limit = 100, string $end = '...'): string
{
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . $end;
}

// ============================================
// Format Helpers
// ============================================

/**
 * Format currency (VND)
 */
function format_money($amount): string
{
    return number_format((float)$amount, 0, ',', '.') . ' ₫';
}

/**
 * Format number
 */
function format_number($number, int $decimals = 0): string
{
    return number_format((float)$number, $decimals, ',', '.');
}

/**
 * Format date
 */
function format_date(?string $date, string $format = 'd/m/Y'): string
{
    if (!$date) return '';
    return date($format, strtotime($date));
}

/**
 * Format datetime
 */
function format_datetime(?string $datetime, string $format = 'd/m/Y H:i'): string
{
    if (!$datetime) return '';
    return date($format, strtotime($datetime));
}

/**
 * Time ago (Vietnamese)
 */
function time_ago(string $datetime): string
{
    $now = time();
    $time = strtotime($datetime);
    $diff = $now - $time;

    if ($diff < 60) return 'Vừa xong';
    if ($diff < 3600) return floor($diff / 60) . ' phút trước';
    if ($diff < 86400) return floor($diff / 3600) . ' giờ trước';
    if ($diff < 604800) return floor($diff / 86400) . ' ngày trước';
    if ($diff < 2592000) return floor($diff / 604800) . ' tuần trước';
    return format_date($datetime);
}

// ============================================
// Order Helpers
// ============================================

/**
 * Generate order code
 */
function generate_order_code(): string
{
    $db = Database::getInstance();
    $date = date('Ymd');
    $prefix = "DH-{$date}-";

    // Get the latest order code for today
    $latest = $db->fetch(
        "SELECT order_code FROM orders WHERE order_code LIKE ? ORDER BY id DESC LIMIT 1",
        [$prefix . '%']
    );

    if ($latest) {
        $lastNumber = (int) substr($latest->order_code, -3);
        $nextNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $nextNumber = '001';
    }

    return $prefix . $nextNumber;
}

/**
 * Get step name by number
 */
function step_name(int $stepNumber): string
{
    $steps = [
        1 => 'Cưa đai',
        2 => 'Thiết kế',
        3 => 'Nung sườn',
        4 => 'Nguội sườn',
        5 => 'Đắp sứ',
        6 => 'Nguội sứ',
        7 => 'Stain màu',
        8 => 'Trả mẫu',
    ];
    return $steps[$stepNumber] ?? 'Không xác định';
}

/**
 * Get status badge HTML
 */
function status_badge(string $status, string $type = 'production'): string
{
    $config = require dirname(__DIR__) . '/config/app.php';
    $key = $type . '_statuses';
    $statuses = $config[$key] ?? [];

    $info = $statuses[$status] ?? ['label' => $status, 'color' => '#6b7280'];
    $label = $info['label'];
    $color = $info['color'];

    return "<span class=\"badge\" style=\"background:{$color}; color:#fff;\">{$label}</span>";
}

/**
 * Priority badge
 */
function priority_badge(string $priority): string
{
    $config = require dirname(__DIR__) . '/config/app.php';
    $info = $config['priorities'][$priority] ?? ['label' => $priority, 'color' => '#6b7280'];
    return "<span class=\"badge badge-{$info['badge']}\">{$info['label']}</span>";
}

// ============================================
// Flash Message Helpers
// ============================================

/**
 * Set flash message
 */
function flash(string $key, string $message): void
{
    $_SESSION["flash_{$key}"] = $message;
}

/**
 * Get and clear flash message
 */
function get_flash(string $key): ?string
{
    $message = $_SESSION["flash_{$key}"] ?? null;
    unset($_SESSION["flash_{$key}"]);
    return $message;
}

/**
 * Get old input value
 */
function old(string $key, $default = ''): string
{
    $old = $_SESSION['flash_old'] ?? [];
    $value = $old[$key] ?? $default;
    return e((string) $value);
}

/**
 * Get validation error for field
 */
function error(string $field): string
{
    $errors = $_SESSION['flash_errors'] ?? [];
    if (isset($errors[$field])) {
        $msg = e($errors[$field]);
        return "<span class=\"field-error\">{$msg}</span>";
    }
    return '';
}

/**
 * Check if field has error
 */
function has_error(string $field): bool
{
    $errors = $_SESSION['flash_errors'] ?? [];
    return isset($errors[$field]);
}

/**
 * Clear flash errors after displaying
 */
function clear_flash_errors(): void
{
    unset($_SESSION['flash_errors']);
    unset($_SESSION['flash_old']);
}

// ============================================
// Settings Helpers
// ============================================

/**
 * Get setting value
 */
function setting(string $key, $default = null): ?string
{
    static $cache = [];
    if (isset($cache[$key])) return $cache[$key];

    $db = Database::getInstance();
    $row = $db->fetch("SELECT setting_value FROM settings WHERE setting_key = ?", [$key]);
    $cache[$key] = $row ? $row->setting_value : $default;
    return $cache[$key];
}

// ============================================
// Misc Helpers
// ============================================

/**
 * CSRF token
 */
function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRF field
 */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
}

/**
 * Verify CSRF token
 */
function verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * User avatar URL
 */
function avatar_url(?string $avatar): string
{
    if ($avatar) {
        return url($avatar);
    }
    return asset('images/default-avatar.png');
}

/**
 * Get production progress percentage for an order
 */
function order_progress(int $orderId): int
{
    $db = Database::getInstance();
    $total = $db->count('production_steps', 'order_id = ?', [$orderId]);
    if ($total === 0) return 0;

    $completed = $db->count('production_steps', 'order_id = ? AND status = ?', [$orderId, 'completed']);
    return (int) round(($completed / $total) * 100);
}

/**
 * Get current production step for an order
 */
function current_step(int $orderId): ?object
{
    $db = Database::getInstance();
    return $db->fetch(
        "SELECT * FROM production_steps WHERE order_id = ? AND status IN ('in_progress', 'waiting') ORDER BY step_number ASC LIMIT 1",
        [$orderId]
    );
}

/**
 * Days remaining until due date
 */
function days_remaining(?string $dueDate): int
{
    if (!$dueDate) return 0;
    $now = new DateTime();
    $due = new DateTime($dueDate);
    $diff = $now->diff($due);
    return $diff->invert ? -$diff->days : $diff->days;
}

/**
 * Due date status class
 */
function due_date_class(?string $dueDate): string
{
    $days = days_remaining($dueDate);
    if ($days < 0) return 'text-danger';
    if ($days <= 1) return 'text-warning';
    return 'text-success';
}
