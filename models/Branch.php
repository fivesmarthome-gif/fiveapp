<?php
/**
 * Branch Model
 * HoanKiem LAB
 */

class Branch extends BaseModel
{
    protected static string $table = 'branches';

    /**
     * Get active branches
     */
    public static function getActive(): array
    {
        return self::where("is_active = 1", [], "name ASC");
    }
}
