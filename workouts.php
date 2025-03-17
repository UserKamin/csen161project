<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
libxml_use_internal_errors(true);
libxml_clear_errors();

// Check if the database exists
if (!file_exists('workouts.db')) {
    echo "Error: No Database found.";
    exit;
}

// Simulate a logged-in user (for demonstration purposes)
$userId = 1; // Replace with actual user ID from session or authentication

try {
    // Connect to the SQLite database
    $pdo = new PDO('sqlite:workouts.db');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch all exercises
    $query = "SELECT * FROM exercises";
    $stmt = $pdo->prepare($query);
    $stmt->execute();

    $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch user's workout plans
    $query = "SELECT * FROM users_workouts WHERE user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $workoutPlans = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
    exit;
}

// Load the HTML file
$htmlFile = 'workouts.html';
if (!file_exists($htmlFile)) {
    echo "Error: workouts.html file not found.";
    exit;
}


$doc = new DOMDocument();
$doc->loadHTMLFile($htmlFile);

// Get the exercises container
$exercisesContainer = $doc->getElementById('exercises');

// Loop through each exercise and create HTML elements
foreach ($exercises as $exercise) {
    $exerciseId = $exercise['id'];
    $exerciseName = $exercise['name'];
    $muscleGroups = $exercise['muscle_groups'];
    $difficulty = $exercise['difficulty'];
    $image = $exercise['image'];
    $link = $exercise['link'];

    // Create exercise container
    $exerciseElement = $doc->createElement('div');
    $exerciseElement->setAttribute('class', 'exercise');

    // Exercise name
    $nameElement = $doc->createElement('h2', htmlspecialchars($exerciseName));

    // Muscle groups
    $muscleGroupsElement = $doc->createElement('p', 'Muscle Groups: ' . htmlspecialchars($muscleGroups));

    // Difficulty
    $difficultyElement = $doc->createElement('p', 'Difficulty: ' . htmlspecialchars($difficulty));

    // Image
    $imageElement = $doc->createElement('img');
    $imageElement->setAttribute('src', "images/$image");
    $imageElement->setAttribute('alt', $exerciseName);

    // Link to how to perform the exercise
    $linkElement = $doc->createElement('a', 'How to Perform');
    $linkElement->setAttribute('href', $link);
    $linkElement->setAttribute('target', '_blank');

    // Add to Workout button
    $addToWorkoutButton = $doc->createElement('button', 'Add to Workout');
    $addToWorkoutButton->setAttribute('class', 'add-to-workout-button');
    $addToWorkoutButton->setAttribute('onclick', "showWorkoutPlansModal($exerciseId)");

    // Append elements to the exercise container
    $exerciseElement->appendChild($nameElement);
    $exerciseElement->appendChild($muscleGroupsElement);
    $exerciseElement->appendChild($difficultyElement);
    $exerciseElement->appendChild($imageElement);
    $exerciseElement->appendChild($linkElement);
    $exerciseElement->appendChild($addToWorkoutButton);

    // Append the exercise to the container
    $exercisesContainer->appendChild($exerciseElement);
}

// Create the workout plans modal
$modal = $doc->createElement('div');
$modal->setAttribute('id', 'workoutPlansModal');
$modal->setAttribute('class', 'modal');

// Modal content
$modalContent = $doc->createElement('div');
$modalContent->setAttribute('class', 'modal-content');

// Modal header
$modalHeader = $doc->createElement('div');
$modalHeader->setAttribute('class', 'modal-header');
$modalHeader->appendChild($doc->createElement('h2', 'Add to Workout Plan'));

// Close button
$closeButton = $doc->createElement('button', '×');
$closeButton->setAttribute('class', 'close-button');
$closeButton->setAttribute('onclick', "closeWorkoutPlansModal()");
$modalHeader->appendChild($closeButton);

// Modal body
$modalBody = $doc->createElement('div');
$modalBody->setAttribute('class', 'modal-body');

// List of workout plans
if (!empty($workoutPlans)) {
    foreach ($workoutPlans as $workout) {
        $workoutId = $workout['workout_id'];
        $workoutName = $workout['workout_name'];

        $workoutPlanButton = $doc->createElement('button', htmlspecialchars($workoutName));
        $workoutPlanButton->setAttribute('class', 'workout-plan-button');
        $workoutPlanButton->setAttribute('onclick', "addExerciseToWorkout($workoutId)");

        $modalBody->appendChild($workoutPlanButton);
    }
} else {
    $modalBody->appendChild($doc->createElement('p', 'No workout plans found.'));
}

// Append modal content
$modalContent->appendChild($modalHeader);
$modalContent->appendChild($modalBody);
$modal->appendChild($modalContent);

// Append the modal to the body
$body = $doc->getElementsByTagName('body')->item(0);
$body->appendChild($modal);

// Output the modified HTML
header('Content-Type: text/html');
echo $doc->saveHTML();
?>