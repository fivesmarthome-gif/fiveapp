<?php
/**
 * Notification Model
 * HoanKiem LAB
 */

class Notification extends BaseModel
{
    protected static string $table = 'notifications';

    /**
     * Get unread notifications for a user
     */
    public static function getUnread(int $userId): array
    {
        return self::where("user_id = ? AND is_read = 0", [$userId], "id DESC");
    }

    /**
     * Mark a notification as read
     */
    public static function markAsRead(int $id): bool
    {
        return self::update($id, ['is_read' => 1]) > 0;
    }

    /**
     * Mark all notifications as read for a user
     */
    public static function markAllAsRead(int $userId): int
    {
        $db = Database::getInstance();
        return $db->update('notifications', ['is_read' => 1], 'user_id = ? AND is_read = 0', [$userId]);
    }
}
