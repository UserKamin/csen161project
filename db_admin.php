<?php
session_start();
require_once 'db_config.php';

// Handle logout request
if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: db_admin.php');
    exit;
}

// Basic admin authentication (you should implement proper admin authentication)
if (!isset($_SESSION['admin']) && (!isset($_POST['admin_password']) || $_POST['admin_password'] !== 'admin123')) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Admin Login</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
        <div class="container">
            <div class="login-form">
                <h1>Database Admin</h1>
                <?php if (isset($_GET['error'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn">Login</button>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
} else {
    $_SESSION['admin'] = true;
}

// Handle user deletion if requested
if (isset($_GET['delete_user'])) {
    $userId = $_GET['delete_user'];
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        header('Location: db_admin.php?message=User deleted successfully');
        exit;
    } catch(PDOException $e) {
        header('Location: db_admin.php?error=' . urlencode('Error deleting user: ' . $e->getMessage()));
        exit;
    }
}

// Fetch all users
try {
    $stmt = $pdo->query("SELECT user_id, username FROM users");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching users: ' . $e->getMessage();
    $users = [];
}

// Fetch all workouts
try {
    $stmt = $pdo->query("SELECT user_id, workout_id, workout_name FROM users_workouts");
    $workouts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching workouts: ' . $e->getMessage();
    $workouts = [];
}

// Fetch all exercises in workouts
try {
    $stmt = $pdo->query("SELECT workout_id, exercise_id FROM workout_exercises");
    $workout_exercises = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching workout_exercises: ' . $e->getMessage();
    $workout_exercises = [];
}

// Fetch all workouts in calendars
try {
    $stmt = $pdo->query("SELECT user_id, workout_id, day_index FROM calendar_workouts");
    $calendar_workouts = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching calendar_workouts: ' . $e->getMessage();
    $calendar_workouts = [];
}

// Fetch all exercises
try {
    $stmt = $pdo->query("SELECT id, name, muscle_groups, difficulty FROM exercises");
    $exercises = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching exercises: ' . $e->getMessage();
    $exercises = [];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Database Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .admin-actions {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .admin-actions a {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .admin-actions a.danger {
            background-color: #f44336;
        }
        .workout-details {
            max-height: 150px;
            overflow-y: auto;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Database Admin Panel</h1>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="success-message"><?php echo htmlspecialchars($_GET['message']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>
        
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <h2>Users</h2>
        <?php if (empty($users)): ?>
            <p>No users found in the database.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td>
                            <a href="db_admin.php?delete_user=<?= $user['user_id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this user?')" 
                               class="danger">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2>Workouts</h2>
        <?php if (empty($workouts)): ?>
            <p>No workouts found in the database.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>User ID</th>
                    <th>Workout_id</th>
                    <th>Workout_Name</th>
                </tr>
                <?php foreach ($workouts as $workout): ?>
                    <tr>
                        <td><?= $workout['user_id'] ?></td>
                        <td><?= $workout['workout_id'] ?></td>
                        <td><?= htmlspecialchars($workout['workout_name']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2>Exercises In Workouts</h2>
        <?php if (empty($workout_exercises)): ?>
            <p>No exercises in workouts found in the database.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Workout_id</th>
                    <th>Exercise_id</th>
                </tr>
                <?php foreach ($workout_exercises as $workout_exercise): ?>
                    <tr>
                        <td><?= $workout_exercise['workout_id'] ?></td>
                        <td><?= $workout_exercise['exercise_id'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2>Workouts in Calendar</h2>
        <?php if (empty($calendar_workouts)): ?>
            <p>No workouts in calendars found in the database.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>User_id</th>
                    <th>Workout_id</th>
                    <th>Day Index</th>
                </tr>
                <?php foreach ($calendar_workouts as $calendar_workout): ?>
                    <tr>
                        <td><?= $calendar_workout['user_id'] ?></td>
                        <td><?= $calendar_workout['workout_id'] ?></td>
                        <td><?= $calendar_workout['day_index'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>

        <h2>Exercises</h2>
        <?php if (empty($exercises)): ?>
            <p>No workouts found in the database.</p>
        <?php else: ?>
            <table>
                <tr>
                    <th>Exercises ID</th>
                    <th>Exercise Name</th>
                    <th>Muscle Groups</th>
                    <th>Difficulty</th>
                </tr>
                <?php foreach ($exercises as $exercise): ?>
                    <tr>
                        <td><?= $exercise['id'] ?></td>
                        <td><?= htmlspecialchars($exercise['name']) ?></td>
                        <td><?= htmlspecialchars($exercise['muscle_groups']) ?></td>
                        <td><?= htmlspecialchars($exercise['difficulty']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php endif; ?>
        
        <div class="admin-actions">
            <a href="index.html">Back to Login</a>
            <a href="db_admin.php?logout=1" class="danger">Logout</a>
        </div>
    </div>
</body>
</html>

