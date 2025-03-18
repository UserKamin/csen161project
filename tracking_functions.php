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
    
    if ($action === 'add_entry') {
        addEntry();
    } elseif ($action === 'update_entry') {
        updateEntry();
    } elseif ($action === 'delete_entry') {
        deleteEntry();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_tracking_data') {
        getTrackingData();
    }
}

// Function to add a new tracking entry
function addEntry() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    $date = $_POST['date'] ?? '';
    $weight = $_POST['weight'] !== '' ? $_POST['weight'] : null;
    $calories = $_POST['calories'] !== '' ? $_POST['calories'] : null;
    $steps = $_POST['steps'] !== '' ? $_POST['steps'] : null;
    
    if (empty($date)) {
        echo json_encode(['success' => false, 'message' => 'Date is required']);
        return;
    }
    
    if ($weight === null && $calories === null && $steps === null) {
        echo json_encode(['success' => false, 'message' => 'At least one of Weight, Calories, or Steps must be provided']);
        return;
    }
    
    try {
        // Check if entry for this date already exists
        $stmt = $pdo->prepare("SELECT id FROM tracking WHERE user_id = ? AND date = ?");
        $stmt->execute([$userId, $date]);
        
        if ($stmt->rowCount() > 0) {
            // Update existing entry
            $entry = $stmt->fetch();
            $entryId = $entry['id'];
            
            $stmt = $pdo->prepare("UPDATE tracking SET weight = ?, calories = ?, steps = ? WHERE id = ?");
            $stmt->execute([$weight, $calories, $steps, $entryId]);
        } else {
            // Insert new entry
            $stmt = $pdo->prepare("INSERT INTO tracking (user_id, date, weight, calories, steps) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$userId, $date, $weight, $calories, $steps]);
        }
        
        echo json_encode(['success' => true]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to update an existing tracking entry
function updateEntry() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $date = $_POST['date'] ?? '';
    $weight = $_POST['weight'] !== '' ? $_POST['weight'] : null;
    $calories = $_POST['calories'] !== '' ? $_POST['calories'] : null;
    $steps = $_POST['steps'] !== '' ? $_POST['steps'] : null;
    
    if ($id <= 0 || empty($date)) {
        echo json_encode(['success' => false, 'message' => 'ID and Date are required']);
        return;
    }
    
    try {
        // Direct update approach - simpler and less prone to errors
        $stmt = $pdo->prepare("UPDATE tracking SET date = ?, weight = ?, calories = ?, steps = ? WHERE id = ? AND user_id = ?");
        $result = $stmt->execute([$date, $weight, $calories, $steps, $id, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Entry not found or no changes made']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to delete a tracking entry
function deleteEntry() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    
    // Debug information
    error_log("Delete Entry - ID: $id, User ID: $userId");
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID is required']);
        return;
    }
    
    try {
        // Direct delete approach
        $stmt = $pdo->prepare("DELETE FROM tracking WHERE id = ? AND user_id = ?");
        $stmt->execute([$id, $userId]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Entry not found or already deleted']);
        }
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to get tracking data
function getTrackingData() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    
    try {
        // Get all tracking entries for the user
        $stmt = $pdo->prepare("SELECT id, date, weight, calories, steps FROM tracking WHERE user_id = ? ORDER BY date DESC");
        $stmt->execute([$userId]);
        $data = $stmt->fetchAll();
        
        echo json_encode(['success' => true, 'data' => $data]);
        
    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>

