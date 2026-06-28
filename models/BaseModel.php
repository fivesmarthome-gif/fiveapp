<?php
/**
 * Base Model Class
 * HoanKiem LAB
 */

abstract class BaseModel
{
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /**
     * Get all records
     */
    public static function all(string $orderBy = ''): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        return $db->fetchAll($sql);
    }

    /**
     * Find record by ID
     */
    public static function find($id): ?object
    {
        $db = Database::getInstance();
        return $db->fetch("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }

    /**
     * Create a new record
     */
    public static function create(array $data): int
    {
        $db = Database::getInstance();
        // Remove created_at and updated_at if not in database
        return $db->insert(static::$table, $data);
    }

    /**
     * Update record by ID
     */
    public static function update($id, array $data): int
    {
        $db = Database::getInstance();
        return $db->update(static::$table, $data, static::$primaryKey . " = ?", [$id]);
    }

    /**
     * Delete record by ID
     */
    public static function delete($id): int
    {
        $db = Database::getInstance();
        return $db->delete(static::$table, static::$primaryKey . " = ?", [$id]);
    }

    /**
     * Count records matching criteria
     */
    public static function count(string $where = '1=1', array $params = []): int
    {
        $db = Database::getInstance();
        return $db->count(static::$table, $where, $params);
    }

    /**
     * Fetch records matching criteria
     */
    public static function where(string $where, array $params = [], string $orderBy = ''): array
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE " . $where;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        return $db->fetchAll($sql, $params);
    }

    /**
     * Fetch first record matching criteria
     */
    public static function first(string $where, array $params = [], string $orderBy = ''): ?object
    {
        $db = Database::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE " . $where;
        if ($orderBy) {
            $sql .= " ORDER BY " . $orderBy;
        }
        $sql .= " LIMIT 1";
        return $db->fetch($sql, $params);
    }
}
