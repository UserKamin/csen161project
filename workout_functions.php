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
    } elseif ($action === 'rename_workout') {
        renameWorkout();
    } elseif ($action === 'add_new_workout') {
        addNewWorkout();
    } elseif ($action === 'delete_workout') {
        deleteWorkout();
    } elseif ($action === 'import_workout') {
        importWorkout();
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';
    
    if ($action === 'get_workouts') {
        getWorkouts();
    }
}

// Function to import a workout template from a file
function importWorkout() {
    global $pdo;

    $userId = $_SESSION['user_id'];
    $workoutName = $_POST['workout_name'] ?? '';
    $workoutData = $_POST['workout_data'] ?? '';

    if (empty($workoutName) || empty($workoutData)) {
        echo json_encode(['success' => false, 'message' => 'Workout name and data are required']);
        return;
    }

    try {
        // Decode imported workout data
        $importedWorkout = json_decode($workoutData, true);

        // Validate the imported workout structure
        if (!is_array($importedWorkout) || count($importedWorkout) !== 7) {
            echo json_encode(['success' => false, 'message' => 'Invalid workout data format']);
            return;
        }

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

        // Add imported workout to the user's workouts
        $workouts[$workoutName] = $importedWorkout;

        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);

        echo json_encode(['success' => true, 'workouts' => $workouts]);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to add a workout activity to a specific day in a workout template
function addWorkout() {
    global $pdo;
    
    $userId = $_SESSION['user_id'];
    $workoutName = $_POST['workout_name'] ?? '';
    $dayIndex = $_POST['day_index'] ?? '';
    $workoutActivity = $_POST['workout_activity'] ?? '';

    if (empty($workoutName) || $dayIndex === '' || empty($workoutActivity)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
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

        // Add workout activity to the specified day in the specified workout
        if (!isset($workouts[$workoutName])) {
            $workouts[$workoutName] = array_fill(0, 7, []);
        }
        $workouts[$workoutName][$dayIndex][] = $workoutActivity;

        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);

        echo json_encode(['success' => true, 'workouts' => $workouts]);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to rename a workout template
function renameWorkout() {
    global $pdo;

    $userId = $_SESSION['user_id'];
    $oldName = $_POST['old_name'] ?? '';
    $newName = $_POST['new_name'] ?? '';

    if (empty($oldName) || empty($newName)) {
        echo json_encode(['success' => false, 'message' => 'Old and new names are required']);
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

        // Rename the workout template
        if (isset($workouts[$oldName])) {
            $workouts[$newName] = $workouts[$oldName];
            unset($workouts[$oldName]);
        }

        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);

        echo json_encode(['success' => true, 'workouts' => $workouts]);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to add a new empty workout template
function addNewWorkout() {
    global $pdo;

    $userId = $_SESSION['user_id'];
    $workoutName = $_POST['workout_name'] ?? '';

    if (empty($workoutName)) {
        echo json_encode(['success' => false, 'message' => 'Workout name is required']);
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

        // Add new empty workout template
        $workouts[$workoutName] = array_fill(0, 7, []);

        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);

        echo json_encode(['success' => true, 'workouts' => $workouts]);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to delete a workout template
function deleteWorkout() {
    global $pdo;

    $userId = $_SESSION['user_id'];
    $workoutName = $_POST['workout_name'] ?? '';

    if (empty($workoutName)) {
        echo json_encode(['success' => false, 'message' => 'Workout name is required']);
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

        // Delete the specified workout template
        if (isset($workouts[$workoutName])) {
            unset($workouts[$workoutName]);
        }

        // Update workouts in database
        $stmt = $pdo->prepare("UPDATE users SET workouts = ? WHERE user_id = ?");
        $stmt->execute([json_encode($workouts), $userId]);

        echo json_encode(['success' => true, 'workouts' => $workouts]);

    } catch(PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Function to get all workouts for the user
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