<?php
// Database file
$databaseFile = 'workout_planner.db';

// Create or open the database using PDO
try {
    $pdo = new PDO('sqlite:' . $databaseFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set default fetch mode to associative array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    $pdo->exec('CREATE TABLE IF NOT EXISTS users (
        user_id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL
    )');

    // Create the exercises table
    $pdo->exec('CREATE TABLE IF NOT EXISTS exercises (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        muscle_groups TEXT NOT NULL,
        difficulty TEXT NOT NULL,
        image TEXT NOT NULL,
        link TEXT NOT NULL
    )');

    // Read the JSON file
    $jsonData = file_get_contents('exercises.json');
    $data = json_decode($jsonData, true);

    // Insert data into the database
    foreach ($data['exercises'] as $exercise) {
        $name = $exercise['name'];
        $muscleGroups = implode(', ', $exercise['muscle_groups']);
        $difficulty = $exercise['difficulty'];
        $image = $exercise['image'];
        $link = $exercise['link'];

        $stmt = $pdo->prepare('INSERT INTO exercises (name, muscle_groups, difficulty, image, link) VALUES (:name, :muscle_groups, :difficulty, :image, :link)');
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':muscle_groups', $muscleGroups, PDO::PARAM_STR);
        $stmt->bindValue(':difficulty', $difficulty, PDO::PARAM_STR);
        $stmt->bindValue(':image', $image, PDO::PARAM_STR);
        $stmt->bindValue(':link', $link, PDO::PARAM_STR);
        $stmt->execute();
    }

    // Create the users_workouts table
    $pdo->exec('CREATE TABLE IF NOT EXISTS users_workouts (
        user_id INTEGER NOT NULL,
        workout_id INTEGER PRIMARY KEY AUTOINCREMENT,
        workout_name TEXT NOT NULL
    )');

    // Create the workout_exercises table
    $pdo->exec('CREATE TABLE IF NOT EXISTS workout_exercises (
        workout_id INTEGER NOT NULL,
        exercise_id INTEGER NOT NULL,
        FOREIGN KEY (workout_id) REFERENCES users_workouts(workout_id)
    )');

    $pdo->exec('CREATE TABLE IF NOT EXISTS calendar_workouts (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        workout_id INTEGER NOT NULL,
        day_index INTEGER NOT NULL,
        FOREIGN KEY (user_id) REFERENCES users(user_id),
        FOREIGN KEY (workout_id) REFERENCES users_workouts(workout_id)
    )');

    echo "Database setup complete!";
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage();
}
?>

