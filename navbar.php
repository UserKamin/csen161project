<nav>
    <a href="home.php">Home</a>
    <a href="muscle.php">Muscle</a>
    <a href="planner.php">Planner</a>
    <a href="tracking.php">Tracking</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a>
        <a href="register.php">Register</a>
    <?php endif; ?>
</nav>