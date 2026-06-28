<?php
/**
 * OrderItem Model
 * HoanKiem LAB
 */

class OrderItem extends BaseModel
{
    protected static string $table = 'order_items';

    /**
     * Get production steps for this order item
     */
    public static function getProductionSteps(int $orderItemId): array
    {
        return ProductionStep::where("order_item_id = ?", [$orderItemId], "step_number ASC");
    }

    /**
     * Initialize the 8 production steps for this item
     */
    public static function initializeSteps(int $orderItemId, int $orderId): bool
    {
        $db = Database::getInstance();
        $appConfig = require dirname(__DIR__) . '/config/app.php';
        $steps = $appConfig['production_steps'] ?? [];

        // Check if steps already exist
        $exists = $db->count('production_steps', 'order_item_id = ?', [$orderItemId]) > 0;
        if ($exists) return true;

        $db->beginTransaction();
        try {
            foreach ($steps as $number => $info) {
                // Find primary staff for this step at the order's branch
                // Default to null if not found
                $order = $db->fetch("SELECT branch_id FROM orders WHERE id = ?", [$orderId]);
                $branchId = $order ? $order->branch_id : null;

                $assignedUser = null;
                if ($branchId) {
                    $staff = $db->fetch(
                        "SELECT user_id FROM production_assignments 
                         WHERE step_name = ? AND branch_id = ? AND is_primary = 1 LIMIT 1",
                        [$info['name'], $branchId]
                    );
                    $assignedUser = $staff ? $staff->user_id : null;
                }

                $db->insert('production_steps', [
                    'order_item_id'   => $orderItemId,
                    'order_id'        => $orderId,
                    'step_number'     => $number,
                    'step_name'       => $info['name'],
                    'assigned_to'     => $assignedUser,
                    'status'          => ($number === 1) ? 'waiting' : 'waiting', // Default all waiting
                    'estimated_hours' => $info['estimated_hours'] ?? null,
                ]);
            }
            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}
