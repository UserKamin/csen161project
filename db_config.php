<?php
// Database configuration using SQLite
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
    
} catch(PDOException $e) {
    die("ERROR: Could not connect or create database. " . $e->getMessage());
}
?>

