<?php
/**
 * Admin Feedback Controller
 * HoanKiem LAB
 */

class FeedbackController extends Controller
{
    public function index(): void
    {
        $feedbacks = $this->db->fetchAll(
            "SELECT f.*, o.order_code, c.name AS customer_name, c.clinic_name
             FROM order_feedbacks f
             JOIN orders o ON o.id = f.order_id
             JOIN users c ON c.id = f.customer_id
             ORDER BY f.created_at DESC"
        );

        $this->view('admin.feedbacks.index', [
            'pageTitle' => 'Quản lý phản hồi khách hàng',
            'breadcrumbs' => ['Dashboard' => url('/admin/dashboard'), 'Phản hồi' => ''],
            'feedbacks' => $feedbacks
        ]);
    }

    public function show(string $id): void
    {
        $feedback = $this->db->fetch(
            "SELECT f.*, o.order_code, c.name AS customer_name, c.clinic_name, c.phone AS customer_phone
             FROM order_feedbacks f
             JOIN orders o ON o.id = f.order_id
             JOIN users c ON c.id = f.customer_id
             WHERE f.id = ?",
            [(int)$id]
        );

        if (!$feedback) { $this->redirect('/admin/feedbacks'); return; }

        $this->view('admin.feedbacks.show', [
            'pageTitle' => "Phản hồi đơn #{$feedback->order_code}",
            'breadcrumbs' => ['Phản hồi' => url('/admin/feedbacks'), $feedback->order_code => ''],
            'feedback' => $feedback
        ]);
    }

    public function reply(string $id): void
    {
        if (!verify_csrf()) { $this->redirect("/admin/feedbacks/{$id}"); return; }

        $reply = trim($this->input('admin_reply', ''));
        if (empty($reply)) {
            $this->redirect("/admin/feedbacks/{$id}", ['error' => 'Nội dung phản hồi không được để trống']);
            return;
        }

        $feedback = $this->db->fetch("SELECT * FROM order_feedbacks WHERE id=?", [(int)$id]);
        if (!$feedback) { $this->redirect('/admin/feedbacks'); return; }

        $this->db->update('order_feedbacks', [
            'admin_reply' => $reply,
            'replied_by' => Auth::getInstance()->id(),
            'replied_at' => date('Y-m-d H:i:s'),
            'status' => 'resolved'
        ], 'id = ?', [$feedback->id]);

        // Notify customer
        $this->notify($feedback->customer_id, 'order_feedback',
            "Phản hồi của bạn đã được trả lời",
            $reply,
            ['feedback_id' => $feedback->id]
        );

        $this->redirect("/admin/feedbacks/{$id}", ['success' => 'Đã gửi câu trả lời thành công']);
    }
}
