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
        
} catch(PDOException $e) {
    die("ERROR: Could not connect or create database. " . $e->getMessage());
}
?>