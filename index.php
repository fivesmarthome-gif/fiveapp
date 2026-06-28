<?php
/**
 * HoanKiem LAB - Entry Point
 * Dental Lab Management System
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    $appConfig = require __DIR__ . '/config/app.php';
    ini_set('session.cookie_lifetime', $appConfig['session_lifetime'] ?? 2592000);
    ini_set('session.gc_maxlifetime', $appConfig['session_lifetime'] ?? 2592000);
    session_name($appConfig['session_name'] ?? 'hklab_session');
    session_start();
}

// Set timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Class Autoloader
spl_autoload_register(function ($class) {
    // Autoload from core/
    $coreFile = __DIR__ . '/core/' . $class . '.php';
    if (file_exists($coreFile)) {
        require_once $coreFile;
        return;
    }

    // Autoload from models/
    $modelFile = __DIR__ . '/models/' . $class . '.php';
    if (file_exists($modelFile)) {
        require_once $modelFile;
        return;
    }
});

// Load helper functions
if (file_exists(__DIR__ . '/core/helpers.php')) {
    require_once __DIR__ . '/core/helpers.php';
}

// Automatically check and initialize the database if tables are missing
try {
    $db = Database::getInstance();
    // Check if a standard table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'users'")->rowCount();
    if ($tableCheck === 0) {
        // Tables are missing, let's run the schema.sql
        $schemaFile = __DIR__ . '/database/schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            // Split SQL by semicolons, but ignore semicolons inside quotes
            // A simple way is to use PDO's exec for the whole file if supported,
            // or split queries by statement.
            // Since schema.sql might contain multiple statements, let's execute it query by query.
            $pdo = $db->getPdo();
            $pdo->exec($sql);
        }
    }
} catch (PDOException $e) {
    // If database connection fails because database doesn't exist, try to create it
    if ($e->getCode() == 1049) { // Unknown database
        try {
            $dbConfig = require __DIR__ . '/config/database.php';
            $tempDsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};charset={$dbConfig['charset']}";
            $tempPdo = new PDO($tempDsn, $dbConfig['username'], $dbConfig['password']);
            $tempPdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $tempPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['database']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Re-instantiate Database and import schema
            $db = Database::getInstance();
            $schemaFile = __DIR__ . '/database/schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                $db->getPdo()->exec($sql);
            }
        } catch (Exception $ex) {
            die("Database Initialization Error: " . $ex->getMessage());
        }
    } else {
        die("Database Connection Error: " . $e->getMessage());
    }
}

// Try remember token login if cookie exists and not logged in
Auth::getInstance()->tryRememberLogin();

// Instantiate Router
$router = new Router();

// Load routes
if (file_exists(__DIR__ . '/routes/web.php')) {
    require_once __DIR__ . '/routes/web.php';
} else {
    die("Routes file 'routes/web.php' is missing.");
}

// Dispatch the request
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];
$router->dispatch($method, $uri);
