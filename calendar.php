<?php
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTMLFile('calendar.html');
libxml_clear_errors(); // Optional: Clear errors after loading

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit;
}

require_once 'db_config.php';

try {
    $userId = $_SESSION['user_id'];

    // Fetch user's workouts
    $stmt = $pdo->prepare("SELECT workout_id, workout_name FROM users_workouts WHERE user_id = ?");
    $stmt->execute([$userId]);
    $workouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch workouts assigned to specific days
    $stmt = $pdo->prepare("
        SELECT c.day_index, w.workout_id, w.workout_name 
        FROM calendar_workouts c
        JOIN users_workouts w ON c.workout_id = w.workout_id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $calendarWorkouts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Organize workouts by day index
    $organizedWorkouts = array_fill(0, 7, []);
    foreach ($calendarWorkouts as $workout) {
        $organizedWorkouts[$workout['day_index']][] = [
            'id' => $workout['workout_id'],
            'name' => $workout['workout_name']
        ];
    }

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    exit;
}

// Load the HTML file
$htmlFile = 'calendar.html';
if (!file_exists($htmlFile)) {
    echo "Error: calendar.html file not found.";
    exit;
}

$doc = new DOMDocument();
$doc->loadHTMLFile($htmlFile);

// Get the calendar container
$calendarContainer = $doc->getElementById('workout-calendar');

// Days of the week
$days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Loop through each day and create HTML elements
foreach ($days as $index => $day) {
    $dayElement = $doc->createElement('div');
    $dayElement->setAttribute('class', 'calendar-day');

    // Day header
    $dayHeader = $doc->createElement('div');
    $dayHeader->setAttribute('class', 'day-header');
    $dayHeader->appendChild($doc->createTextNode($day));
    $dayElement->appendChild($dayHeader);

    // Workout list
    $workoutList = $doc->createElement('div');
    $workoutList->setAttribute('class', 'workout-list');
    $workoutList->setAttribute('id', 'day-' . $index);

    // Add workouts for this day if they exist
    if (!empty($organizedWorkouts[$index])) {
        foreach ($organizedWorkouts[$index] as $workout) {
            $workoutId = $workout['id'];

            $workoutTag = $doc->createElement('div');
            $workoutTag->setAttribute('class', 'workout-tag');
            $workoutTag->setAttribute('data-workout-id', $workoutId); // Add workout ID for deletion

            // Workout name
            $workoutName = $doc->createTextNode($workout['name']);
            $workoutTag->appendChild($workoutName);

            // Delete button
            $deleteButton = $doc->createElement('span', '×');
            $deleteButton->setAttribute('class', 'delete-workout');
            $deleteButton->setAttribute('onclick', "deleteWorkoutFromCalendar($workoutId, $index)");
            $workoutTag->appendChild($deleteButton);

            $workoutList->appendChild($workoutTag);
        }
    }

    $dayElement->appendChild($workoutList);

    // Add Workout button
    $addButton = $doc->createElement('button');
    $addButton->setAttribute('class', 'add-workout-btn');
    $addButton->appendChild($doc->createTextNode('+ Add Workout'));
    $addButton->setAttribute('onclick', "openWorkoutModal('$day', $index)");
    $dayElement->appendChild($addButton);

    // Append the day to the calendar container
    $calendarContainer->appendChild($dayElement);
}

// Output the modified HTML
header('Content-Type: text/html');
echo $doc->saveHTML();
?>