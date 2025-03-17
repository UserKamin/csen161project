<?php
session_start();
require_once 'db_config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        header('Location: index.html?error=' . urlencode('Username and password are required'));
        exit;
    }
    
    try {
        // Check if user exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            header('Location: index.html?error=' . urlencode('Invalid username or password. Please try again.'));
            exit;
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        
        // Redirect to home page
        header('Location: home.php');
        exit;
        
    } catch(PDOException $e) {
        header('Location: index.html?error=' . urlencode('Database error: ' . $e->getMessage()));
        exit;
    }
} else {
    // If not a POST request, redirect to the login page
    header('Location: index.html');
    exit;
}
?>

