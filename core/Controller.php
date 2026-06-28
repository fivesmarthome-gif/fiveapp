<?php
/**
 * Base Controller
 * HoanKiem LAB
 */

class Controller
{
    protected Database $db;
    protected array $appConfig;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->appConfig = require dirname(__DIR__) . '/config/app.php';
    }

    /**
     * Render a view with optional layout support
     *
     * Views can set $layout to a layout name (e.g. 'layouts.admin') to wrap
     * their content. $pageTitle and $breadcrumbs are passed to the layout.
     */
    protected function view(string $view, array $data = []): void
    {
        // Add common data available in all views
        $auth          = Auth::getInstance();
        $currentUser   = $auth->user();
        $appConfig     = $this->appConfig;
        $db            = $this->db;

        // Get unread notifications count
        $unreadNotifications = 0;
        if ($currentUser) {
            $unreadNotifications = $this->db->count('notifications', 'user_id = ? AND is_read = 0', [$currentUser->id]);
        }

        // Make caller-supplied data available
        extract($data);

        // Convert view name to file path (dot notation: admin.dashboard -> views/admin/dashboard.php)
        $viewPath = dirname(__DIR__) . '/views/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewPath)) {
            throw new Exception("View not found: {$view} ({$viewPath})");
        }

        // Determine layout: views set $layout; fall back to auto-detect by prefix
        if (!isset($layout)) {
            if (str_starts_with($view, 'admin.')) {
                $layout = 'layouts.admin';
            } elseif (str_starts_with($view, 'customer.')) {
                $layout = 'layouts.customer';
            } elseif (str_starts_with($view, 'staff.')) {
                $layout = 'layouts.staff';
            } elseif (str_starts_with($view, 'shipper.')) {
                $layout = 'layouts.shipper';
            } else {
                $layout = null; // No layout (auth pages render standalone)
            }
        }

        if ($layout) {
            // Buffer view content
            ob_start();
            require $viewPath;
            $content = ob_get_clean();

            // Render layout
            $layoutPath = dirname(__DIR__) . '/views/' . str_replace('.', '/', $layout) . '.php';
            if (!file_exists($layoutPath)) {
                throw new Exception("Layout not found: {$layout} ({$layoutPath})");
            }
            require $layoutPath;
        } else {
            require $viewPath;
        }
    }

    /**
     * Return JSON response
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Redirect to URL
     */
    protected function redirect(string $path, array $flash = []): void
    {
        foreach ($flash as $key => $value) {
            $_SESSION["flash_{$key}"] = $value;
        }
        header('Location: ' . url($path));
        exit;
    }

    /**
     * Get POST input
     */
    protected function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    /**
     * Get all POST data
     */
    protected function allInput(): array
    {
        return array_merge($_GET, $_POST);
    }

    /**
     * Validate inputs
     */
    protected function validate(array $rules): array
    {
        $errors = [];
        $data = [];

        foreach ($rules as $field => $ruleString) {
            $value = $this->input($field);
            $fieldRules = explode('|', $ruleString);
            $label = str_replace('_', ' ', $field);

            foreach ($fieldRules as $rule) {
                $ruleParts = explode(':', $rule, 2);
                $ruleName = $ruleParts[0];
                $ruleParam = $ruleParts[1] ?? null;

                switch ($ruleName) {
                    case 'required':
                        if (empty($value) && $value !== '0') {
                            $errors[$field] = "Vui lòng nhập {$label}";
                        }
                        break;
                    case 'email':
                        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$field] = "Email không hợp lệ";
                        }
                        break;
                    case 'phone':
                        if ($value && !preg_match('/^0[0-9]{9,10}$/', $value)) {
                            $errors[$field] = "Số điện thoại không hợp lệ";
                        }
                        break;
                    case 'min':
                        if ($value && strlen($value) < (int)$ruleParam) {
                            $errors[$field] = "{$label} tối thiểu {$ruleParam} ký tự";
                        }
                        break;
                    case 'max':
                        if ($value && strlen($value) > (int)$ruleParam) {
                            $errors[$field] = "{$label} tối đa {$ruleParam} ký tự";
                        }
                        break;
                    case 'numeric':
                        if ($value && !is_numeric($value)) {
                            $errors[$field] = "{$label} phải là số";
                        }
                        break;
                    case 'date':
                        if ($value && !strtotime($value)) {
                            $errors[$field] = "{$label} không đúng định dạng ngày";
                        }
                        break;
                    case 'unique':
                        if ($value) {
                            $parts = explode(',', $ruleParam);
                            $table = $parts[0];
                            $column = $parts[1] ?? $field;
                            $exceptId = $parts[2] ?? null;
                            $where = "{$column} = ?";
                            $params = [$value];
                            if ($exceptId) {
                                $where .= " AND id != ?";
                                $params[] = $exceptId;
                            }
                            if ($this->db->count($table, $where, $params) > 0) {
                                $errors[$field] = "{$label} đã tồn tại";
                            }
                        }
                        break;
                    case 'in':
                        if ($value && !in_array($value, explode(',', $ruleParam))) {
                            $errors[$field] = "{$label} không hợp lệ";
                        }
                        break;
                    case 'confirmed':
                        $confirmField = $field . '_confirmation';
                        if ($value !== $this->input($confirmField)) {
                            $errors[$field] = "{$label} xác nhận không khớp";
                        }
                        break;
                }

                if (isset($errors[$field])) break;
            }

            $data[$field] = $value;
        }

        if (!empty($errors)) {
            $_SESSION['flash_errors'] = $errors;
            $_SESSION['flash_old'] = $data;
        }

        return ['valid' => empty($errors), 'data' => $data, 'errors' => $errors];
    }

    /**
     * Handle file upload
     */
    protected function uploadFile(string $fieldName, string $subDir = ''): ?string
    {
        if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $file = $_FILES[$fieldName];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        // Check file size
        if ($file['size'] > $this->appConfig['max_upload_size']) {
            $_SESSION['flash_error'] = 'File quá lớn (tối đa 10MB)';
            return null;
        }

        // Check allowed extensions
        if (!in_array($ext, $this->appConfig['allowed_files'])) {
            $_SESSION['flash_error'] = 'Định dạng file không được phép';
            return null;
        }

        $uploadDir = dirname(__DIR__) . '/' . $this->appConfig['upload_dir'];
        if ($subDir) {
            $uploadDir .= '/' . $subDir;
        }

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $fileName = uniqid() . '_' . time() . '.' . $ext;
        $filePath = $uploadDir . '/' . $fileName;

        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $relativePath = $this->appConfig['upload_dir'] . ($subDir ? "/{$subDir}" : '') . '/' . $fileName;
            return $relativePath;
        }

        return null;
    }

    /**
     * Upload multiple files
     */
    protected function uploadMultipleFiles(string $fieldName, string $subDir = ''): array
    {
        $paths = [];
        if (!isset($_FILES[$fieldName])) return $paths;

        $files = $_FILES[$fieldName];
        if (!is_array($files['name'])) return $paths;

        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;

            $_FILES['_temp_upload'] = [
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];

            $path = $this->uploadFile('_temp_upload', $subDir);
            if ($path) $paths[] = $path;
        }

        return $paths;
    }

    /**
     * Create notification
     */
    protected function notify(int $userId, string $type, string $title, string $content = '', array $data = []): void
    {
        $this->db->insert('notifications', [
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'content' => $content,
            'data'    => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
    }

    /**
     * Log activity
     */
    protected function logActivity(string $action, string $modelType = '', int $modelId = 0, string $description = ''): void
    {
        $user = Auth::getInstance()->user();
        $this->db->insert('activity_logs', [
            'user_id'     => $user ? $user->id : null,
            'action'      => $action,
            'model_type'  => $modelType,
            'model_id'    => $modelId,
            'description' => $description,
            'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
            'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
    }
}
