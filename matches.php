<?php
session_start();
require_once 'includes/dbs.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Get matches
$stmt = $db->prepare("SELECT animals.* FROM matches 
    JOIN animals ON matches.animal_id = animals.id
    WHERE matches.user_id = ?");
$stmt->execute([$user_id]);
$matches = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Matches - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Your Matches</h1>
    
    <?php if (empty($matches)): ?>
        <p>You don't have any matches yet. Start swiping!</p>
    <?php else: ?>
        <div class="match-grid">
            <?php foreach ($matches as $match): ?>
                <div class="match-card">
                    <?php if ($match['picture']): ?>
                        <img src="<?= htmlspecialchars($match['picture']) ?>" alt="<?= htmlspecialchars($match['name']) ?>">
                    <?php endif; ?>
                    <h2><?= htmlspecialchars($match['name']) ?></h2>
                    <p>Breed: <?= htmlspecialchars($match['breed']) ?></p>
                    <p>Age: <?= htmlspecialchars($match['age']) ?> years</p>
                    <p><?= htmlspecialchars($match['description']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <p><a href="dashboard.php">Back to Dashboard</a></p>
</body>
</html>