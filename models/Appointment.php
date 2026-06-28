<?php
/**
 * Appointment Model
 * HoanKiem LAB
 */

class Appointment extends BaseModel
{
    protected static string $table = 'appointments';

    /**
     * Get appointments for a customer
     */
    public static function getForCustomer(int $customerId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.name as staff_name, b.name as branch_name 
             FROM appointments a 
             LEFT JOIN users u ON a.staff_id = u.id 
             LEFT JOIN branches b ON a.branch_id = b.id 
             WHERE a.customer_id = ? 
             ORDER BY a.appointment_date DESC",
            [$customerId]
        );
    }

    /**
     * Get appointments for a staff member
     */
    public static function getForStaff(int $staffId): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT a.*, u.name as customer_name, u.phone as customer_phone, b.name as branch_name 
             FROM appointments a 
             JOIN users u ON a.customer_id = u.id 
             LEFT JOIN branches b ON a.branch_id = b.id 
             WHERE a.staff_id = ? 
             ORDER BY a.appointment_date ASC",
            [$staffId]
        );
    }
}
