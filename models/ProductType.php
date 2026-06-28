<?php
/**
 * ProductType Model
 * HoanKiem LAB
 */

class ProductType extends BaseModel
{
    protected static string $table = 'product_types';

    /**
     * Get active product types
     */
    public static function getActive(): array
    {
        return self::where("is_active = 1", [], "sort_order ASC, name ASC");
    }

    /**
     * Get categories list
     */
    public static function getCategories(): array
    {
        $db = Database::getInstance();
        return $db->fetchAll("SELECT DISTINCT category FROM product_types WHERE category IS NOT NULL AND is_active = 1");
    }
}
