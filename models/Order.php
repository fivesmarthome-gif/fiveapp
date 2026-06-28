<?php
/**
 * Order Model
 * HoanKiem LAB
 */

class Order extends BaseModel
{
    protected static string $table = 'orders';

    /**
     * Find order by code
     */
    public static function findByCode(string $code): ?object
    {
        return self::first("order_code = ?", [$code]);
    }

    /**
     * Find order by due date token (for adjustments without login)
     */
    public static function findByToken(string $token): ?object
    {
        return self::first("due_date_token = ?", [$token]);
    }

    /**
     * Get order items
     */
    public static function getItems(int $orderId): array
    {
        return OrderItem::where("order_id = ?", [$orderId]);
    }

    /**
     * Get order attachments
     */
    public static function getAttachments(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT * FROM order_attachments WHERE order_id = ? ORDER BY id DESC", [$orderId]);
    }

    /**
     * Get order feedbacks
     */
    public static function getFeedbacks(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT f.*, u.name as customer_name, u.avatar as customer_avatar 
             FROM order_feedbacks f 
             JOIN users u ON f.customer_id = u.id 
             WHERE f.order_id = ? 
             ORDER BY f.id DESC",
            [$orderId]
        );
    }

    /**
     * Get order history timeline logs
     */
    public static function getTimeline(int $orderId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT l.*, u.name as changer_name, u.role as changer_role 
             FROM order_status_logs l 
             LEFT JOIN users u ON l.changed_by = u.id 
             WHERE l.order_id = ? 
             ORDER BY l.id ASC",
            [$orderId]
        );
    }

    /**
     * Update order status and log the change
     */
    public static function updateStatus(int $orderId, string $statusType, string $newStatus, ?int $userId, string $notes = ''): bool
    {
        $db = Database::getInstance();
        $order = self::find($orderId);
        if (!$order) return false;

        $field = match ($statusType) {
            'production' => 'production_status',
            'delivery'   => 'delivery_status',
            'overall'    => 'overall_status',
            default      => null
        };

        if (!$field) return false;

        $oldStatus = $order->$field;
        if ($oldStatus === $newStatus) return true; // No change

        $db->beginTransaction();
        try {
            // Update orders table
            $db->update('orders', [$field => $newStatus], 'id = ?', [$orderId]);

            // Add status log entry
            $db->insert('order_status_logs', [
                'order_id'    => $orderId,
                'from_status' => $oldStatus,
                'to_status'   => $newStatus,
                'status_type' => $statusType,
                'changed_by'  => $userId,
                'notes'       => $notes
            ]);

            // Auto-update overall status based on production/delivery if necessary
            if ($statusType === 'production' && $newStatus === 'ready' && $order->delivery_status === 'none') {
                $db->update('orders', ['delivery_status' => 'waiting_pickup'], 'id = ?', [$orderId]);
                $db->insert('order_status_logs', [
                    'order_id'    => $orderId,
                    'from_status' => 'none',
                    'to_status'   => 'waiting_pickup',
                    'status_type' => 'delivery',
                    'changed_by'  => $userId,
                    'notes'       => 'Hệ thống tự động chuyển: Sản xuất hoàn thành, chờ giao.'
                ]);
            }

            if ($statusType === 'delivery') {
                if ($newStatus === 'delivered') {
                    $db->update('orders', ['overall_status' => 'completed'], 'id = ?', [$orderId]);
                    $db->insert('order_status_logs', [
                        'order_id'    => $orderId,
                        'from_status' => $order->overall_status,
                        'to_status'   => 'completed',
                        'status_type' => 'overall',
                        'changed_by'  => $userId,
                        'notes'       => 'Hệ thống tự động hoàn thành đơn hàng sau khi giao thành công.'
                    ]);
                } elseif ($newStatus === 'returned') {
                    // Re-production flow or cancel
                    // Custom implementation based on needs
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}
