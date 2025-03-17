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

    // Fetch workout plans for the user
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
$htmlFile = 'workout_planner.html';
if (!file_exists($htmlFile)) {
    echo "Error: workout_planner.html file not found.";
    exit;
}

$doc = new DOMDocument();
$doc->loadHTMLFile($htmlFile);

// Get the workout plans container
$workoutPlansContainer = $doc->getElementById('workoutPlans');

if (empty($workoutPlans)) {
    // Display message if no workout plans exist
    $noPlansMessage = $doc->createElement('p', 'No workout plans! Add a new workout plan.');
    $noPlansMessage->setAttribute('class', 'no-plans-message');
    $workoutPlansContainer->appendChild($noPlansMessage);
} else {
    // Loop through each workout plan and create HTML elements
    foreach ($workoutPlans as $workout) {
        $workoutId = $workout['workout_id'];
        $workoutName = $workout['workout_name'];

        // Create workout plan container
        $workoutElement = $doc->createElement('div');
        $workoutElement->setAttribute('class', 'workout-plan');
        $workoutElement->setAttribute('data-workout-id', $workoutId);

        // Workout name
        $nameElement = $doc->createElement('h2', htmlspecialchars($workoutName));

        // Fetch exercises for this workout
        $query = "SELECT exercises.id, exercises.name FROM workout_exercises
                  JOIN exercises ON workout_exercises.exercise_id = exercises.id
                  WHERE workout_exercises.workout_id = :workout_id";
        $stmt = $pdo->prepare($query);
        $stmt->bindValue(':workout_id', $workoutId, PDO::PARAM_INT);
        $stmt->execute();

        $exercises = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Exercises list (displayed as tags)
        $exercisesContainer = $doc->createElement('div');
        $exercisesContainer->setAttribute('class', 'exercises-container');
        foreach ($exercises as $exercise) {
            $exerciseId = $exercise['id'];
            $exerciseName = $exercise['name'];

            // Create exercise tag
            $exerciseTag = $doc->createElement('div');
            $exerciseTag->setAttribute('class', 'exercise-tag');
            $exerciseTag->setAttribute('data-exercise-id', $exerciseId);

            // Exercise name
            $exerciseNameElement = $doc->createElement('span', htmlspecialchars($exerciseName));

            // Delete button for the exercise
            $deleteButton = $doc->createElement('button', '×');
            $deleteButton->setAttribute('class', 'delete-exercise-button');
            $deleteButton->setAttribute('onclick', "deleteExerciseFromWorkout($workoutId, $exerciseId)");

            // Append elements to the exercise tag
            $exerciseTag->appendChild($exerciseNameElement);
            $exerciseTag->appendChild($deleteButton);

            // Append the exercise tag to the exercises container
            $exercisesContainer->appendChild($exerciseTag);
        }

        // Delete workout button
        $deleteWorkoutButton = $doc->createElement('button', 'Delete Workout');
        $deleteWorkoutButton->setAttribute('class', 'delete-workout-button');
        $deleteWorkoutButton->setAttribute('onclick', "deleteWorkout($workoutId)");

        // Append elements to the workout plan container
        $workoutElement->appendChild($nameElement);
        $workoutElement->appendChild($exercisesContainer);
        $workoutElement->appendChild($deleteWorkoutButton);

        // Append the workout plan to the container
        $workoutPlansContainer->appendChild($workoutElement);
    }
}

// Output the modified HTML
header('Content-Type: text/html');
echo $doc->saveHTML();
?>