<?php
/**
 * User Model
 * HoanKiem LAB
 */

class User extends BaseModel
{
    protected static string $table = 'users';

    /**
     * Find user by phone number
     */
    public static function findByPhone(string $phone): ?object
    {
        return self::first("phone = ?", [$phone]);
    }

    /**
     * Get users by role
     */
    public static function getByRole(string $role): array
    {
        return self::where("role = ? AND is_active = 1", [$role], "name ASC");
    }

    /**
     * Get staff assigned to a specific step
     */
    public static function getStaffForStep(string $stepName): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT u.* FROM users u 
             JOIN production_assignments pa ON u.id = pa.user_id 
             WHERE pa.step_name = ? AND u.is_active = 1",
            [$stepName]
        );
    }
}
