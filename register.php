<?php
require_once 'includes/dbs.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $user_type = $_POST['user_type'];
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, email, user_type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $email, $user_type]);
        
        // Create empty profile
        $user_id = $db->lastInsertId();
        $stmt = $db->prepare("INSERT INTO profiles (user_id) VALUES (?)");
        $stmt->execute([$user_id]);
        
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $error = "Username or email already exists";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="user_type" required>
                <option value="human">Pet Seeker</option>
                <option value="shelter">Shelter</option>
            </select>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>