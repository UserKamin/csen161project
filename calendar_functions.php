<?php
session_start();
require_once 'db_config.php';

$action = $_GET['action'] ?? $_POST['action'] ?? null;

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

try {
    $userId = $_SESSION['user_id'];

    switch ($action) {
        case 'get_user_workouts':
            // Fetch all workouts created by the user
            $stmt = $pdo->prepare("SELECT workout_id, workout_name FROM users_workouts WHERE user_id = ?");
            $stmt->execute([$userId]);
            $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'workouts' => $workouts]);
            break;

        case 'add_calendar_workout':
            // Add a workout to a specific day
            $dayIndex = $_POST['day_index'] ?? null;
            $workoutId = $_POST['workout_id'] ?? null;

            if (!$dayIndex || !$workoutId) {
                echo json_encode(['success' => false, 'message' => 'Missing day_index or workout_id']);
                exit;
            }

            // Insert into calendar_workouts table
            $stmt = $pdo->prepare("INSERT INTO calendar_workouts (user_id, workout_id, day_index) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $workoutId, $dayIndex]);

            echo json_encode(['success' => true]);
            break;
        case 'delete_calendar_workout':
    // Delete a workout from the calendar
    $workoutId = $_POST['workout_id'] ?? null;
    $dayIndex = $_POST['day_index'] ?? null;

    if (!$workoutId || !$dayIndex) {
        echo json_encode(['success' => false, 'message' => 'Missing workout_id or day_index']);
        exit;
    }

    try {
        // Delete the workout from the calendar_workouts table
        $stmt = $pdo->prepare("DELETE FROM calendar_workouts WHERE workout_id = ? AND day_index = ?");
        $stmt->execute([$workoutId, $dayIndex]);

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
    break;

    case 'delete_calendar_workout':
        // Delete a workout from the calendar
        $workoutId = $_POST['workout_id'] ?? null;
        $dayIndex = $_POST['day_index'] ?? null;
    
        if (!$workoutId || !$dayIndex) {
            echo json_encode(['success' => false, 'message' => 'Missing workout_id or day_index']);
            exit;
        }
    
        try {
            // Delete the workout from the calendar_workouts table
            $stmt = $pdo->prepare("DELETE FROM calendar_workouts WHERE workout_id = ? AND day_index = ?");
            $stmt->execute([$workoutId, $dayIndex]);
    
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
        break;

        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
            break;
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>