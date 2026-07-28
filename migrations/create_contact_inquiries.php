<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

try {
    $pdo = getDatabase();
    
    $sql = "
    CREATE TABLE IF NOT EXISTS contact_inquiries (
        inquiry_id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(100) NOT NULL,
        last_name VARCHAR(100) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        status ENUM('pending', 'read', 'resolved') DEFAULT 'pending',
        assigned_admin_id INT NULL,
        internal_notes TEXT NULL,
        response TEXT NULL,
        response_date DATETIME NULL,
        priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
        source VARCHAR(50) DEFAULT 'Landing Page',
        FOREIGN KEY (assigned_admin_id) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($sql);
    echo "Table contact_inquiries created successfully.\n";
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}
?>
