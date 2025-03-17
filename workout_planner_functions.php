<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

// Check if the database exists
if (!file_exists('workout_planner.db')) {
    echo "Error: No Database found.";
    exit;
}

$userId = $_SESSION['user_id'];

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$functionName = $data['functionName'] ?? null;

// Check if the function name is provided
if ($functionName && function_exists($functionName)) {
    // Call the function and pass the arguments
    $result = $functionName($data);
    echo json_encode($result);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid function name']);
    exit;
}

// Function to add a new workout plan
function addWorkout($data) {
    global $userId;

    $workoutName = $data['workoutName'] ?? null;

    if (!$workoutName) {
        return ['success' => false, 'message' => 'Missing workoutName'];
    }

    try {
        // Connect to the SQLite database
        $pdo = new PDO('sqlite:workout_planner.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the new workout plan
        $query = "INSERT INTO users_workouts (user_id, workout_name) VALUES (:user_id, :workout_name)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':workout_name', $workoutName, PDO::PARAM_STR);
        $stmt->execute();

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to delete a workout plan
function deleteWorkout($data) {
    $workoutId = $data['workoutId'] ?? null;

    if (!$workoutId) {
        return ['success' => false, 'message' => 'Missing workoutId'];
    }

    try {
        // Connect to the SQLite database
        $pdo = new PDO('sqlite:workout_planner.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Begin a transaction
        $pdo->beginTransaction();

        // Delete all exercises associated with the workout
        $query = "DELETE FROM workout_exercises WHERE workout_id = :workout_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
        $stmt->execute();

        // Delete the workout plan
        $query = "DELETE FROM users_workouts WHERE workout_id = :workout_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
        $stmt->execute();

        // Commit the transaction
        $pdo->commit();

        return ['success' => true];
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to add an exercise to a workout
function addExerciseToWorkout($data) {
    $workoutId = $data['workoutId'] ?? null;
    $exerciseId = $data['exerciseId'] ?? null;

    if (!$workoutId || !$exerciseId) {
        return ['success' => false, 'message' => 'Missing workoutId or exerciseId'];
    }

    try {
        // Connect to the SQLite database
        $pdo = new PDO('sqlite:workout_planner.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Insert the exercise into the workout
        $query = "INSERT INTO workout_exercises (workout_id, exercise_id) VALUES (:workout_id, :exercise_id)";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
        $stmt->bindValue(':exercise_id', $exerciseId, PDO::PARAM_INT);
        $stmt->execute();

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

// Function to delete an exercise from a workout
function deleteExerciseFromWorkout($data) {
    $workoutId = $data['workoutId'] ?? null;
    $exerciseId = $data['exerciseId'] ?? null;

    if (!$workoutId || !$exerciseId) {
        return ['success' => false, 'message' => 'Missing workoutId or exerciseId'];
    }

    try {
        // Connect to the SQLite database
        $pdo = new PDO('sqlite:workout_planner.db');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Delete the exercise from the workout
        $query = "DELETE FROM workout_exercises WHERE workout_id = :workout_id AND exercise_id = :exercise_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
        $stmt->bindValue(':exercise_id', $exerciseId, PDO::PARAM_INT);
        $stmt->execute();

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>