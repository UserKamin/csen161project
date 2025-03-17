<?php
// Database configuration
$host = 'localhost';
$dbname = 'workout_planner';
$username = 'root';
$password = '';

try {
    // Create PDO instance
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    die("ERROR: Could not connect. " . $e->getMessage());
}

// Initialize the database
function initializeDatabase() {
    global $pdo;

    try {
        // Create users table
        $sql = "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            workouts JSON DEFAULT '{}'
        )";
        $pdo->exec($sql);
    } catch(PDOException $e) {
        echo "Error initializing database: " . $e->getMessage();
    }
}

initializeDatabase();

