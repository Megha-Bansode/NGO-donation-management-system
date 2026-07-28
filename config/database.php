<?php
/**
 * Shared PDO Database Connection Helper
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../core/Logger.php';

$pdoInstance = null;

function getDatabase() {
    global $pdoInstance;
    
    if ($pdoInstance === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            $pdoInstance = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdoInstance;
}

function closeConnection() {
    global $pdoInstance;
    $pdoInstance = null;
}
