<?php
/**
 * Customer Notification Controller
 * HoanKiem LAB
 */

class NotificationController extends Controller
{
    public function index(): void
    {
        $auth     = Auth::getInstance();
        $customer = $auth->user();

        $notifications = $this->db->fetchAll(
            "SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50",
            [$customer->id]
        );

        // Mark all as read
        $this->db->update('notifications', ['is_read' => 1], 'user_id=? AND is_read=0', [$customer->id]);

        $this->view('customer.notifications', [
            'pageTitle'     => 'Thông báo',
            'backUrl'       => url('/customer/dashboard'),
            'notifications' => $notifications,
        ]);
    }

    public function readAll(): void
    {
        $auth = Auth::getInstance();
        $this->db->update('notifications', ['is_read' => 1], 'user_id=?', [$auth->id()]);
        $this->json(['success' => true]);
    }

    public function markRead(string $id): void
    {
        $auth = Auth::getInstance();
        $this->db->update('notifications', ['is_read' => 1], 'id=? AND user_id=?', [(int)$id, $auth->id()]);
        $this->json(['success' => true]);
    }
}
