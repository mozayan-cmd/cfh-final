<?php
// Simple script to create admin user without Laravel autoloader
$dbPath = __DIR__ . '/database/admin.sqlite';

// Create database if it doesn't exist
if (!file_exists($dbPath)) {
    touch($dbPath);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    
    // Create users table
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(255) NOT NULL DEFAULT 'user',
        is_active BOOLEAN NOT NULL DEFAULT 1,
        created_at TIMESTAMP NULL,
        updated_at TIMESTAMP NULL
    )");
    
    // Insert admin user
    $password = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO users (name, email, password, role, is_active, created_at, updated_at) 
                          VALUES (?, ?, ?, ?, ?, datetime('now'), datetime('now'))");
    $stmt->execute(['Administrator', 'admin@example.com', $password, 'admin', 1]);
    
    echo "Admin user created successfully!\n";
    echo "Email: admin@example.com\n";
    echo "Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>