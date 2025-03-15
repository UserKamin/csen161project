<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h2>Welcome, <span id="username"></span></h2>

        <div class="profile">
            <h3>Profile</h3>
            <p>Name: <span id="name"></span></p>
            <p>Age: <span id="age"></span></p>
            <p>Weight: <span id="weight"></span></p>
            <p>Biography: <span id="biography"></span></p>
        </div>

        <div class="planner">
            <h3>Weekly Planner</h3>
            <table id="weekly-planner">
                <thead>
                    <tr>
                        <th>Day</th>
                        <th>Muscle Group</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dynamically populated via AJAX -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Fetch user profile details via AJAX
            $.ajax({
                url: 'fetch_profile.php',
                method: 'GET',
                success: function (data) {
                    const user = JSON.parse(data);
                    $('#username').text(user.name);
                    $('#name').text(user.name);
                    $('#age').text(user.age || 'N/A');
                    $('#weight').text(user.weight || 'N/A');
                    $('#biography').text(user.biography || 'No biography added.');
                },
                error: function () {
                    alert('Error fetching profile details.');
                }
            });

            // Fetch weekly planner via AJAX
            $.ajax({
                url: 'fetch_planner.php',
                method: 'GET',
                success: function (data) {
                    const planner = JSON.parse(data);
                    let rows = '';
                    planner.forEach(day => {
                        rows += `<tr><td>${day.day_of_week}</td><td>${day.muscle_name || 'Rest Day'}</td></tr>`;
                    });
                    $('#weekly-planner tbody').html(rows);
                },
                error: function () {
                    alert('Error fetching planner details.');
                }
            });
        });
    </script>
</body>
</html>