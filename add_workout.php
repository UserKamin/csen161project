<?php
header('Content-Type: application/json');

// Check if the database exists
if (!file_exists('workouts.db')) {
    echo json_encode(['success' => false, 'message' => 'Database not found']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$workoutName = $data['workoutName'];

// Simulate a logged-in user (for demonstration purposes)
$userId = 1; // Replace with actual user ID from session or authentication

try {
    // Connect to the SQLite database
    $pdo = new PDO('sqlite:workouts.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert the new workout plan into the database
    $query = "INSERT INTO users_workouts (user_id, workout_name) VALUES (:user_id, :workout_name)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':workout_name', $workoutName, PDO::PARAM_STR);
    $stmt->execute();

    $workoutID = 2;
    $exerciseID = 8;

    $query = "INSERT INTO workout_exercises (workout_id, exercise_id) VALUES (:workout_id, :exercise_id)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':workout_id', $workoutID, PDO::PARAM_INT);
    $stmt->bindValue(':exercise_id', $exerciseID, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>