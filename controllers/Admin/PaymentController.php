<?php
/**
 * Admin Payment Controller
 * HoanKiem LAB
 */

class PaymentController extends Controller
{
    public function store(): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/orders'); return; }

        $orderId = (int)$this->input('order_id');
        $amount = (float)$this->input('amount');
        $method = $this->input('method', 'transfer');
        $notes = $this->input('notes', '');

        $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [$orderId]);
        if (!$order) { $this->redirect('/admin/orders'); return; }

        if ($amount <= 0) {
            $this->redirect("/admin/orders/{$orderId}", ['error' => 'Số tiền thanh toán phải lớn hơn 0']);
            return;
        }

        $this->db->beginTransaction();
        try {
            $paymentId = $this->db->insert('payments', [
                'order_id' => $orderId,
                'customer_id' => $order->customer_id,
                'amount' => $amount,
                'method' => $method,
                'status' => 'confirmed', // Auto-confirm by Admin
                'notes' => $notes,
                'confirmed_by' => Auth::getInstance()->id(),
                'paid_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Update order paid amount
            $newPaidAmount = $order->paid_amount + $amount;
            $netAmount = $order->total_amount - $order->discount;

            $paymentStatus = 'partial';
            if ($newPaidAmount >= $netAmount) {
                $paymentStatus = 'paid';
            } elseif ($newPaidAmount <= 0) {
                $paymentStatus = 'unpaid';
            }

            $this->db->update('orders', [
                'paid_amount' => $newPaidAmount,
                'payment_status' => $paymentStatus
            ], 'id = ?', [$orderId]);

            // If customer has balance/credit, we can sync it
            $this->db->query("UPDATE users SET balance = balance - ? WHERE id = ?", [$amount, $order->customer_id]);

            $this->db->commit();
            $this->logActivity('create_payment', 'payments', $paymentId, "Ghi nhận thanh toán " . format_money($amount) . " cho đơn {$order->order_code}");
            $this->redirect("/admin/orders/{$orderId}", ['success' => 'Đã ghi nhận thanh toán thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect("/admin/orders/{$orderId}", ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }

    public function confirm(string $id): void
    {
        if (!verify_csrf()) { $this->redirect('/admin/dashboard'); return; }

        $payment = $this->db->fetch("SELECT * FROM payments WHERE id=?", [(int)$id]);
        if (!$payment || $payment->status === 'confirmed') { $this->redirect('/admin/dashboard'); return; }

        $this->db->beginTransaction();
        try {
            $this->db->update('payments', [
                'status' => 'confirmed',
                'confirmed_by' => Auth::getInstance()->id(),
                'paid_at' => date('Y-m-d H:i:s')
            ], 'id=?', [$payment->id]);

            $order = $this->db->fetch("SELECT * FROM orders WHERE id=?", [$payment->order_id]);
            if ($order) {
                $newPaidAmount = $order->paid_amount + $payment->amount;
                $netAmount = $order->total_amount - $order->discount;
                $paymentStatus = ($newPaidAmount >= $netAmount) ? 'paid' : 'partial';

                $this->db->update('orders', [
                    'paid_amount' => $newPaidAmount,
                    'payment_status' => $paymentStatus
                ], 'id=?', [$order->id]);
            }

            $this->db->commit();
            $this->redirect("/admin/orders/{$payment->order_id}", ['success' => 'Đã xác nhận thanh toán thành công']);
        } catch (Exception $e) {
            $this->db->rollback();
            $this->redirect('/admin/dashboard', ['error' => 'Lỗi: ' . $e->getMessage()]);
        }
    }
}
