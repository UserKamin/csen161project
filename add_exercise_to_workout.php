<?php
header('Content-Type: application/json');

// Check if the database exists
if (!file_exists('workouts.db')) {
    echo json_encode(['success' => false, 'message' => 'Database not found']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$workoutId = $data['workoutId'];
$exerciseId = $data['exerciseId'];

try {
    // Connect to the SQLite database
    $pdo = new PDO('sqlite:workouts.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Insert the exercise into the workout
    $query = "INSERT INTO workout_exercises (workout_id, exercise_id) VALUES (:workout_id, :exercise_id)";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
    $stmt->bindValue(':exercise_id', $exerciseId, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>