<?php
/**
 * Discora - Database Connection Manager (PDO)
 */

require_once __DIR__ . '/constants.php';

class Database {
    private static ?PDO $instance = null;

    private function __construct() {}
    private function __clone() {}

    /**
     * Get single PDO database connection instance
     */
    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // In production, log error to file and display friendly message
                die("Database Connection Error: " . $e->getMessage());
            }
        }
        return self::$instance;
    }
}

/**
 * Helper function for backward compatibility with admin files
 */
function get_db_connection(): PDO {
    return Database::getConnection();
}
