<?php
/**
 * Material Model
 * HoanKiem LAB
 */

class Material extends BaseModel
{
    protected static string $table = 'materials';

    /**
     * Get low stock materials
     */
    public static function getLowStock(): array
    {
        return self::where("current_stock <= min_stock AND is_active = 1", [], "current_stock ASC");
    }

    /**
     * Update stock level
     */
    public static function updateStock(int $materialId, float $quantity, string $transactionType, ?int $userId, string $notes = '', ?int $referenceId = null, ?string $referenceType = null): bool
    {
        $db = Database::getInstance();
        $material = self::find($materialId);
        if (!$material) return false;

        $newStock = $material->current_stock;
        if (in_array($transactionType, ['import', 'return'])) {
            $newStock += $quantity;
        } elseif (in_array($transactionType, ['export', 'adjust'])) {
            if ($transactionType === 'export') {
                $newStock -= $quantity;
            } else {
                // Adjustment: quantity is the direct difference or absolute value depending on convention
                // Let's assume quantity is delta (+/-)
                $newStock += $quantity;
            }
        }

        if ($newStock < 0) return false; // Stock cannot be negative

        $db->beginTransaction();
        try {
            // Update materials table
            self::update($materialId, ['current_stock' => $newStock]);

            // Add material transaction
            $db->insert('material_transactions', [
                'material_id'    => $materialId,
                'type'           => $transactionType,
                'quantity'       => $quantity,
                'unit_cost'      => $material->unit_cost,
                'reference_id'   => $referenceId,
                'reference_type' => $referenceType,
                'performed_by'   => $userId,
                'notes'          => $notes
            ]);

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollback();
            return false;
        }
    }
}
