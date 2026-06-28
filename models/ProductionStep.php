<?php
/**
 * ProductionStep Model
 * HoanKiem LAB
 */

class ProductionStep extends BaseModel
{
    protected static string $table = 'production_steps';

    /**
     * Start a production step
     */
    public static function startStep(int $stepId, int $userId): bool
    {
        $db = Database::getInstance();
        $step = self::find($stepId);
        if (!$step) return false;

        // Can only start if waiting or rework
        if (!in_array($step->status, ['waiting', 'rework'])) return false;

        return self::update($stepId, [
            'status'      => 'in_progress',
            'started_at'  => date('Y-m-d H:i:s'),
            'assigned_to' => $userId,
        ]) > 0;
    }

    /**
     * Complete a production step
     */
    public static function completeStep(int $stepId, int $userId, string $notes = ''): bool
    {
        $db = Database::getInstance();
        $step = self::find($stepId);
        if (!$step) return false;

        if ($step->status !== 'in_progress') return false;

        $db->beginTransaction();
        try {
            // Update this step
            self::update($stepId, [
                'status'       => 'completed',
                'completed_at' => date('Y-m-d H:i:s'),
                'notes'        => $notes,
                'assigned_to'  => $userId, // Ensure the actual worker is recorded
            ]);

            // Try to set the NEXT step for this item to 'waiting' or activate it
            // (In our case, all steps are initialized as 'waiting', but they become ready as previous completes)
            $nextStepNumber = $step->step_number + 1;
            $nextStep = self::first(
                "order_item_id = ? AND step_number = ?",
                [$step->order_item_id, $nextStepNumber]
            );

            // If there's a next step, we can notify the assigned staff (if any)
            if ($nextStep) {
                // If the next step is waiting, keep it waiting (it can now be started).
            } else {
                // No next step! This item is completed.
                $db->update('order_items', ['status' => 'completed'], 'id = ?', [$step->order_item_id]);

                // Check if ALL items in this order are completed
                $totalItems = $db->count('order_items', 'order_id = ?', [$step->order_id]);
                $completedItems = $db->count('order_items', 'order_id = ? AND status = ?', [$step->order_id, 'completed']);

                if ($totalItems === $completedItems) {
                    // All items completed! Update order production status to ready or qc_passed
                    // Let's set it to ready (or qc_passed) and overall status
                    Order::updateStatus($step->order_id, 'production', 'ready', $userId, 'Tất cả các công đoạn sản xuất đã hoàn thành.');
                }
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }

    /**
     * Rework a step (fail QC or need correction)
     * Reverts current step and future steps back to waiting/rework.
     */
    public static function reworkStep(int $stepId, int $userId, string $reason, int $targetStepNumber): bool
    {
        $db = Database::getInstance();
        $step = self::find($stepId);
        if (!$step) return false;

        if ($targetStepNumber < 1 || $targetStepNumber > 8 || $targetStepNumber > $step->step_number) {
            return false;
        }

        $db->beginTransaction();
        try {
            // Update order item status back to in_production
            $db->update('order_items', ['status' => 'in_production'], 'id = ?', [$step->order_item_id]);
            
            // Set order overall/production status back to in_production
            $order = Order::find($step->order_id);
            if ($order && $order->production_status !== 'in_production') {
                Order::updateStatus($step->order_id, 'production', 'in_production', $userId, "Yêu cầu làm lại công đoạn từ bước {$targetStepNumber}. Lý do: {$reason}");
            }

            // Reset steps from $targetStepNumber up to 8
            $db->query(
                "UPDATE production_steps 
                 SET status = 'waiting', started_at = NULL, completed_at = NULL, notes = NULL
                 WHERE order_item_id = ? AND step_number >= ?",
                [$step->order_item_id, $targetStepNumber]
            );

            // Mark the specific target step as 'rework' and save the reason
            $db->query(
                "UPDATE production_steps 
                 SET status = 'rework', rework_reason = ? 
                 WHERE order_item_id = ? AND step_number = ?",
                [$reason, $step->order_item_id, $targetStepNumber]
            );

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}
