<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_type = $_SESSION['user_type'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome to Furever</h1>
        <div class="nav">
            <a href="swiping.php" class="nav-item">Start Swiping</a>
            <a href="matches.php" class="nav-item">View Matches</a>
            <a href="profile.php" class="nav-item">My Profile</a>
            <?php if ($user_type === 'shelter'): ?>
                <a href="shelter_animals.php" class="nav-item">My Animals</a>
            <?php endif; ?>
            <a href="logout.php" class="nav-item">Logout</a>
        </div>
    </div>
</body>
</html>