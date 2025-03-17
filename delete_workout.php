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

try {
    // Connect to the SQLite database
    $pdo = new PDO('sqlite:workouts.db');
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

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Rollback the transaction on error
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>