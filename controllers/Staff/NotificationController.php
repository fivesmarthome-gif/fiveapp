<?php
class NotificationController extends Controller {
    public function index(): void {
        $auth = Auth::getInstance();
        $notifications = $this->db->fetchAll("SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50", [$auth->id()]);
        $this->db->update('notifications', ['is_read' => 1], 'user_id=? AND is_read=0', [$auth->id()]);
        $this->view('staff.notifications', ['pageTitle' => 'Thông báo', 'notifications' => $notifications]);
    }
    public function readAll(): void { $auth = Auth::getInstance(); $this->db->update('notifications', ['is_read' => 1], 'user_id=?', [$auth->id()]); $this->json(['success' => true]); }
}
