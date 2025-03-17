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
    $stmt = $pdo->query("SELECT user_id, username, workouts FROM users");
    $users = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Error fetching users: ' . $e->getMessage();
    $users = [];
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
                    <th>Workout Count</th>
                    <th>Workout Details</th>
                    <th>Actions</th>
                </tr>
                <?php foreach ($users as $user): ?>
                    <?php 
                    $workouts = json_decode($user['workouts'], true);
                    $workoutCount = 0;
                    foreach ($workouts as $day) {
                        $workoutCount += count($day);
                    }
                    ?>
                    <tr>
                        <td><?= $user['user_id'] ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= $workoutCount ?></td>
                        <td>
                            <div class="workout-details">
                                <?php 
                                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                foreach ($workouts as $index => $day): 
                                    if (!empty($day)):
                                ?>
                                    <strong><?= $days[$index] ?>:</strong> 
                                    <?= implode(', ', array_map('htmlspecialchars', $day)) ?><br>
                                <?php 
                                    endif;
                                endforeach; 
                                ?>
                            </div>
                        </td>
                        <td>
                            <a href="db_admin.php?delete_user=<?= $user['user_id'] ?>" 
                               onclick="return confirm('Are you sure you want to delete this user?')" 
                               class="danger">Delete</a>
                        </td>
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

