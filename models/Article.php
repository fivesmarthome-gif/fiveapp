<?php
/**
 * Article Model
 * HoanKiem LAB
 */

class Article extends BaseModel
{
    protected static string $table = 'articles';

    /**
     * Get published articles
     */
    public static function getPublished(string $type = null): array
    {
        if ($type) {
            return self::where("is_published = 1 AND type = ? AND (published_at IS NULL OR published_at <= NOW())", [$type], "published_at DESC, id DESC");
        }
        return self::where("is_published = 1 AND (published_at IS NULL OR published_at <= NOW())", [], "published_at DESC, id DESC");
    }

    /**
     * Get featured articles
     */
    public static function getFeatured(int $limit = 5): array
    {
        $db = Database::getInstance();
        return $db->fetchAll(
            "SELECT * FROM articles 
             WHERE is_published = 1 
             AND is_featured = 1 
             AND (published_at IS NULL OR published_at <= NOW()) 
             ORDER BY published_at DESC, id DESC 
             LIMIT " . (int)$limit
        );
    }
}
