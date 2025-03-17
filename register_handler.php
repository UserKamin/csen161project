<?php
require_once 'db_config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header('Location: register.html?error=' . urlencode('Username and password are required'));
        exit;
    }
    
    if ($password !== $confirm_password) {
        header('Location: register.html?error=' . urlencode('Passwords do not match'));
        exit;
    }
    
    try {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->rowCount() > 0) {
            header('Location: register.html?error=' . urlencode('Username already exists'));
            exit;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashedPassword]);
        
        // Redirect to login page with success message
        header('Location: index.html?registered=true');
        exit;
        
    } catch(PDOException $e) {
        header('Location: register.html?error=' . urlencode('Database error: ' . $e->getMessage()));
        exit;
    }
} else {
    // If not a POST request, redirect to the registration page
    header('Location: register.html');
    exit;
}
?>

