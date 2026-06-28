<?php
/**
 * Promotion Model
 * HoanKiem LAB
 */

class Promotion extends BaseModel
{
    protected static string $table = 'promotions';

    /**
     * Get active promotions
     */
    public static function getActive(): array
    {
        return self::where(
            "is_active = 1 AND start_date <= NOW() AND end_date >= NOW()",
            [],
            "end_date ASC"
        );
    }

    /**
     * Find promotion by code
     */
    public static function findByCode(string $code): ?object
    {
        return self::first("code = ? AND is_active = 1 AND start_date <= NOW() AND end_date >= NOW()", [$code]);
    }
}
