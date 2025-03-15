<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$query = "
    SELECT p.day_of_week, m.name AS muscle_name
    FROM planner p
    LEFT JOIN muscles m ON p.muscle_id = m.id
    WHERE p.user_id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$planner = [];
while ($row = $result->fetch_assoc()) {
    $planner[] = $row;
}

echo json_encode($planner);
?>