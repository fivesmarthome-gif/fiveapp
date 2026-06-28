<?php
/**
 * Delivery Model
 * HoanKiem LAB
 */

class Delivery extends BaseModel
{
    protected static string $table = 'deliveries';

    /**
     * Find delivery by order ID
     */
    public static function findByOrderId(int $orderId): ?object
    {
        return self::first("order_id = ?", [$orderId]);
    }

    /**
     * Update delivery status and sync with order delivery_status
     */
    public static function updateDeliveryStatus(int $deliveryId, string $status, ?int $userId, array $extraData = []): bool
    {
        $db = Database::getInstance();
        $delivery = self::find($deliveryId);
        if (!$delivery) return false;

        $db->beginTransaction();
        try {
            $updateData = ['status' => $status];
            
            if ($status === 'shipped') {
                $updateData['shipped_at'] = date('Y-m-d H:i:s');
            } elseif ($status === 'delivered') {
                $updateData['delivered_at'] = date('Y-m-d H:i:s');
                if (isset($extraData['recipient_name'])) {
                    $updateData['recipient_name'] = $extraData['recipient_name'];
                }
                if (isset($extraData['proof_photo'])) {
                    $updateData['proof_photo'] = $extraData['proof_photo'];
                }
            }

            $updateData = array_merge($updateData, array_intersect_key($extraData, array_flip([
                'courier_name', 'tracking_number', 'recipient_phone', 'notes'
            ])));

            // Update deliveries table
            self::update($deliveryId, $updateData);

            // Sync status to orders table
            $orderDeliveryStatus = match ($status) {
                'pending'        => 'waiting_pickup',
                'shipped'        => 'shipping',
                'delivered'      => 'delivered',
                'return_pending' => 'pending_return',
                'returned'       => 'returned',
                default          => 'none'
            };

            Order::updateStatus($delivery->order_id, 'delivery', $orderDeliveryStatus, $userId, "Trạng thái vận chuyển cập nhật thành: {$status}");

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}
