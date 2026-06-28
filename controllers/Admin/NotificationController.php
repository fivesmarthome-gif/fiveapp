<?php
/**
 * Admin Notification Controller
 * HoanKiem LAB
 */

class NotificationController extends Controller
{
    public function index(): void
    {
        $notifications = $this->db->fetchAll(
            "SELECT n.*, u.name AS receiver_name
             FROM notifications n
             LEFT JOIN users u ON u.id = n.user_id
             ORDER BY n.created_at DESC LIMIT 100"
        );

        $customers = $this->db->fetchAll("SELECT id, name, clinic_name FROM users WHERE role='customer' AND is_active=1 ORDER BY name ASC");

        $this->view('admin.notifications.index', [
            'pageTitle' => 'Gửi thông báo & Push',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Thông báo' => ''],
            'notifications' => $notifications,
            'customers' => $customers
        ]);
    }

    public function send(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/notifications', ['error' => 'CSRF error']); return; }

        $target = $this->input('target', 'all'); // 'all', 'staff', 'customers', or user_id
        $title = trim($this->input('title', ''));
        $content = trim($this->input('content', ''));

        if (empty($title) || empty($content)) {
            $this->redirect('/admin/notifications', ['error' => 'Vui lòng nhập đầy đủ tiêu đề và nội dung']);
            return;
        }

        $userIds = [];
        if ($target === 'all') {
            $users = $this->db->fetchAll("SELECT id FROM users WHERE is_active=1");
            $userIds = array_column($users, 'id');
        } elseif ($target === 'staff') {
            $users = $this->db->fetchAll("SELECT id FROM users WHERE role='staff' AND is_active=1");
            $userIds = array_column($users, 'id');
        } elseif ($target === 'customers') {
            $users = $this->db->fetchAll("SELECT id FROM users WHERE role='customer' AND is_active=1");
            $userIds = array_column($users, 'id');
        } else {
            $userIds = [(int)$target];
        }

        $this->db->beginTransaction();
        try {
            foreach ($userIds as $userId) {
                $this->notify($userId, 'system', $title, $content);
            }
            $this->db->commit();
            $this->redirect('/admin/notifications', ['success' => 'Đã gửi thông báo thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect('/admin/notifications', ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function readAll(): void
    {
        $this->db->update('notifications', ['is_read' => 1], 'user_id=?', [Auth::getInstance()->id()]);
        $this->redirect('/admin/notifications', ['success' => 'Đã đánh dấu tất cả đã đọc']);
    }
}
