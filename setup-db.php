<?php
// Database file
$dbFile = 'workouts.db';

// Create or open the database
$db = new SQLite3($dbFile);

// Create the exercises table
$db->exec('CREATE TABLE IF NOT EXISTS exercises (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    muscle_groups TEXT NOT NULL,
    difficulty TEXT NOT NULL,
    image TEXT NOT NULL,
    link TEXT NOT NULL
)');

// Read the JSON file
$jsonData = file_get_contents('workouts.json');
$data = json_decode($jsonData, true);

// Insert data into the database
foreach ($data['exercises'] as $exercise) {
    $name = $exercise['name'];
    $muscleGroups = implode(', ', $exercise['muscle_groups']);
    $difficulty = $exercise['difficulty'];
    $image = $exercise['image'];
    $link = $exercise['link'];

    $stmt = $db->prepare('INSERT INTO exercises (name, muscle_groups, difficulty, image, link) VALUES (:name, :muscle_groups, :difficulty, :image, :link)');
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $stmt->bindValue(':muscle_groups', $muscleGroups, SQLITE3_TEXT);
    $stmt->bindValue(':difficulty', $difficulty, SQLITE3_TEXT);
    $stmt->bindValue(':image', $image, SQLITE3_TEXT);
    $stmt->bindValue(':link', $link, SQLITE3_TEXT);
    $stmt->execute();
}

// Create the users_workouts table
$db->exec('CREATE TABLE IF NOT EXISTS users_workouts (
    user_id INTEGER NOT NULL,
    workout_id INTEGER PRIMARY KEY AUTOINCREMENT,
    workout_name TEXT NOT NULL
)');

// Create the workout_exercises table
$db->exec('CREATE TABLE IF NOT EXISTS workout_exercises (
    workout_id INTEGER NOT NULL,
    exercise_id INTEGER NOT NULL,
    FOREIGN KEY (workout_id) REFERENCES users_workouts(workout_id)
)');

echo "Database setup complete!";
?>