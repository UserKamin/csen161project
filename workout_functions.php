<?php
session_start();
require_once 'db_config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Handle different actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_workout') {
        addWorkout();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_workouts') {
        getWorkouts();
    }
}

// Function to add a workout
function addWorkout() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    $dayIndex = $_POST['day_index'] ?? '';
    $workoutName = $_POST['workout_name'] ?? '';
    
    if ($dayIndex === '' || empty($workoutName)) {
        echo json_encode(['success' => false, 'message' => 'Day index and workout name are required']);
        return;
    }
    
    try {
        // Get current workouts
        $stmt = $pdo->prepare("SELECT workouts FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Decode workouts JSON
        $workouts = json_decode($user['workouts'], true);
        
        // Add new workout to the specified day
        $workouts[$dayIndex][] = $workoutName;
        
        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);
        
        echo json_encode(['success' => true, 'workouts' => $workouts]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to get workouts
function getWorkouts() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Get workouts
        $stmt = $pdo->prepare("SELECT workouts FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'User not found']);
            return;
        }
        
        // Decode workouts JSON
        $workouts = json_decode($user['workouts'], true);
        
        echo json_encode(['success' => true, 'workouts' => $workouts]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

