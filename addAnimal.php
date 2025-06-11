<?php
session_start();
require_once 'includes/dbs.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'shelter') {
    header('Location: index.php');
    exit;
}

$shelter_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $breed = $_POST['breed'];
    $age = $_POST['age'];
    $description = $_POST['description'];
    
    $picture = '';
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
    
    $stmt = $db->prepare("INSERT INTO animals (shelter_id, name, breed, age, description, picture) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$shelter_id, $name, $breed, $age, $description, $picture]);
    
    $message = "Animal added successfully!";
    header('Location: shelter_animals.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Animal - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Add New Animal</h1>
    <?php if ($message): ?>
        <p style="color: green;"><?= $message ?></p>
    <?php endif; ?>
    
    <form method="POST" enctype="multipart/form-data">
        <label for="name">Name:</label>
        <input type="text" name="name" required>
        
        <label for="breed">Breed:</label>
        <input type="text" name="breed" required>
        
        <label for="age">Age (years):</label>
        <input type="number" name="age" min="0" required>
        
        <label for="description">Description:</label>
        <textarea name="description" required></textarea>
        
        <label for="picture">Picture:</label>
        <input type="file" name="picture" accept="image/*">
        
        <button type="submit">Add Animal</button>
    </form>
    
    <p><a href="shelter_animals.php">Back to My Animals</a></p>
</body>
</html>