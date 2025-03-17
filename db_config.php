<?php
// Database configuration
$useSQLite = true; // Set to false to use MySQL instead of SQLite

if ($useSQLite) {
    // SQLite configuration
    $databaseFile = 'workout_planner.db';

    try {
        // Create PDO instance for SQLite
        $pdo = new PDO('sqlite:' . $databaseFile);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set default fetch mode to associative array
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            user_id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            workouts TEXT
        )";

        $pdo->exec($sql);

    } catch (PDOException $e) {
        die("ERROR: Could not connect or create SQLite database. " . $e->getMessage());
    }
} else {
    // MySQL configuration
    $host = 'localhost';
    $dbname = 'workout_planner';
    $username = 'root';
    $password = '';

    try {
        // Create PDO instance for MySQL
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

        // Set the PDO error mode to exception
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Set default fetch mode to associative array
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Create users table if it doesn't exist
        $sql = "CREATE TABLE IF NOT EXISTS users (
            user_id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            workouts JSON DEFAULT '{}'
        )";

        $pdo->exec($sql);

    } catch (PDOException $e) {
        die("ERROR: Could not connect to MySQL database. " . $e->getMessage());
    }
}

// Initialize the database (optional, can be removed if not needed)
function initializeDatabase() {
    global $pdo;

    try {
        // Additional initialization logic can go here
    } catch (PDOException $e) {
        echo "Error initializing database: " . $e->getMessage();
    }
}

initializeDatabase();
?>