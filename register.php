<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $name = $_POST['name'];
    $age = $_POST['age'];
    $weight = $_POST['weight'];
    $biography = $_POST['biography'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, password_hash, name, age, weight, biography) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssiis", $username, $password_hash, $name, $age, $weight, $biography);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit;
        } else {
            $error = "Registration failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="text" name="name" placeholder="Name">
            <input type="number" name="age" placeholder="Age">
            <input type="number" step="0.1" name="weight" placeholder="Weight">
            <textarea name="biography" placeholder="Biography"></textarea>
            <button type="submit">Register</button>
        </form>
        <a href="login.php">Login here</a>
    </div>
</body>
</html>