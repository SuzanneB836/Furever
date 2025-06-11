<?php
session_start();
require_once 'includes/dbs.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'shelter') {
    header('Location: index.php');
    exit;
}

$shelter_id = $_SESSION['user_id'];

// Get shelter's animals
$stmt = $db->prepare("SELECT * FROM animals WHERE shelter_id = ?");
$stmt->execute([$shelter_id]);
$animals = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>My Animals - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>My Shelter Animals</h1>
    <p><a href="addAnimal.php">Add New Animal</a></p>
    
    <?php if (empty($animals)): ?>
        <p>You haven't added any animals yet.</p>
    <?php else: ?>
        <div class="animal-grid">
            <?php foreach ($animals as $animal): ?>
                <div class="animal-card">
                    <?php if ($animal['picture']): ?>
                        <img src="<?= htmlspecialchars($animal['picture']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>">
                    <?php endif; ?>
                    <h2><?= htmlspecialchars($animal['name']) ?></h2>
                    <p>Breed: <?= htmlspecialchars($animal['breed']) ?></p>
                    <p>Age: <?= htmlspecialchars($animal['age']) ?> years</p>
                    <p><?= htmlspecialchars($animal['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>