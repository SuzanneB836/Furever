<?php
session_start();
require_once 'includes/dbs.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$message = '';

// Get current profile
$stmt = $db->prepare("SELECT * FROM profiles WHERE user_id = ?");
$stmt->execute([$user_id]);
$profile = $stmt->fetch();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = $_POST['bio'];
    
    // Handle file upload
    $picture = $profile['picture'];
    if (isset($_FILES['picture']) && $_FILES['picture']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir);
        }
        
        $filename = uniqid() . '_' . basename($_FILES['picture']['name']);
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($_FILES['picture']['tmp_name'], $targetPath)) {
            $picture = $targetPath;
        }
    }
    
    $stmt = $db->prepare("UPDATE profiles SET bio = ?, picture = ? WHERE user_id = ?");
    $stmt->execute([$bio, $picture, $user_id]);
    $message = "Profile updated successfully!";
    
    // Refresh profile data
    $stmt = $db->prepare("SELECT * FROM profiles WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $profile = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Profile - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>My Profile</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <?php if ($profile['picture']): ?>
            <img src="<?= htmlspecialchars($profile['picture']) ?>" alt="Profile Picture" class="profile-pic">
        <?php endif; ?>
        
        <label for="picture">Profile Picture:</label>
        <input type="file" name="picture" accept="image/*">
        
        <label for="bio">Bio:</label>
        <textarea name="bio" required><?= htmlspecialchars($profile['bio'] ?? '') ?></textarea>
        
        <button type="submit">Update Profile</button>
    </form>
    
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>