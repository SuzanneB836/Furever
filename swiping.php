<?php
session_start();
require_once 'includes/dbs.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Handle swipe action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['animal_id'], $_POST['direction'])) {
    $animal_id = $_POST['animal_id'];
    $direction = $_POST['direction'];

    // For shelter swipes, animal_id is 0 and user_id is set
    if ($user_type === 'shelter' && isset($_POST['user_id'])) {
        $swiped_user_id = $_POST['user_id'];
        // Find one animal belonging to this shelter (or NULL if none)
        $stmt = $db->prepare("SELECT id FROM animals WHERE shelter_id = ? LIMIT 1");
        $stmt->execute([$user_id]);
        $animal_row = $stmt->fetch();
        $shelter_animal_id = $animal_row ? $animal_row['id'] : null;
        if ($shelter_animal_id) {
            // Prevent duplicate swipes
            $stmt = $db->prepare("SELECT COUNT(*) FROM swipes WHERE user_id = ? AND animal_id = ?");
            $stmt->execute([$swiped_user_id, $shelter_animal_id]);
            if ($stmt->fetchColumn() == 0) {
                $stmt = $db->prepare("INSERT INTO swipes (user_id, animal_id, direction) VALUES (?, ?, ?)");
                $stmt->execute([$swiped_user_id, $shelter_animal_id, $direction]);
            }
            // Check for match (if both swiped right)
            if ($direction === 'right') {
                $stmt = $db->prepare("SELECT * FROM swipes WHERE user_id = ? AND animal_id = ? AND direction = 'right'");
                $stmt->execute([$swiped_user_id, $shelter_animal_id]);
                if ($stmt->fetch()) {
                    // Only insert if not already matched
                    $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE user_id = ? AND animal_id = ?");
                    $stmt->execute([$swiped_user_id, $shelter_animal_id]);
                    if ($stmt->fetchColumn() == 0) {
                        $stmt = $db->prepare("INSERT INTO matches (user_id, animal_id) VALUES (?, ?)");
                        $stmt->execute([$swiped_user_id, $shelter_animal_id]);
                    }
                }
            }
        }
    } else {
        // Prevent duplicate swipes
        $stmt = $db->prepare("SELECT COUNT(*) FROM swipes WHERE user_id = ? AND animal_id = ?");
        $stmt->execute([$user_id, $animal_id]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $db->prepare("INSERT INTO swipes (user_id, animal_id, direction) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $animal_id, $direction]);
        }
        // Check for match (if both swiped right)
        if ($direction === 'right') {
            $stmt = $db->prepare("SELECT * FROM swipes 
                WHERE user_id IN (SELECT shelter_id FROM animals WHERE id = ?)
                AND animal_id = ? 
                AND direction = 'right'");
            $stmt->execute([$animal_id, $animal_id]);
            if ($stmt->fetch()) {
                // Only insert if not already matched
                $stmt = $db->prepare("SELECT COUNT(*) FROM matches WHERE user_id = ? AND animal_id = ?");
                $stmt->execute([$user_id, $animal_id]);
                if ($stmt->fetchColumn() == 0) {
                    $stmt = $db->prepare("INSERT INTO matches (user_id, animal_id) VALUES (?, ?)");
                    $stmt->execute([$user_id, $animal_id]);
                }
            }
        }
    }
}

// Get next animal/user to show
$animal = null;
if ($user_type === 'human') {
    $stmt = $db->prepare("SELECT * FROM animals 
        WHERE id NOT IN (SELECT animal_id FROM swipes WHERE user_id = ?)
        AND shelter_id IS NOT NULL
        ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id]);
    $animal = $stmt->fetch();
} elseif ($user_type === 'shelter') {
    // Only show users if this shelter has animals
    $stmt = $db->prepare("SELECT COUNT(*) FROM animals WHERE shelter_id = ?");
    $stmt->execute([$user_id]);
    $animal_count = $stmt->fetchColumn();
    if ($animal_count > 0) {
        $stmt = $db->prepare("SELECT u.* FROM users u
            WHERE u.user_type = 'human'
            AND u.id != ?
            AND NOT EXISTS (
                SELECT 1 FROM swipes s
                JOIN animals a ON s.animal_id = a.id
                WHERE a.shelter_id = ? AND s.user_id = u.id
            )
            ORDER BY u.id DESC LIMIT 1");
        $stmt->execute([$user_id, $user_id]);
        $animal = $stmt->fetch();
    } else {
        $animal = null;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Swiping - Furever</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Find Your Match</h1>
    <?php if ($user_type === 'human' && $animal): ?>
        <div class="card">
            <?php if ($animal['picture']): ?>
                <img src="<?= htmlspecialchars($animal['picture']) ?>" alt="<?= htmlspecialchars($animal['name']) ?>">
            <?php endif; ?>
            <h2><?= htmlspecialchars($animal['name']) ?></h2>
            <p>Breed: <?= htmlspecialchars($animal['breed']) ?></p>
            <p>Age: <?= htmlspecialchars($animal['age']) ?> years</p>
            <p><?= htmlspecialchars($animal['description']) ?></p>
            <form method="POST" class="actions">
                <input type="hidden" name="animal_id" value="<?= $animal['id'] ?>">
                <button type="submit" name="direction" value="left" class="btn dislike">Dislike</button>
                <button type="submit" name="direction" value="right" class="btn like">Like</button>
            </form>
        </div>
    <?php elseif ($user_type === 'shelter' && $animal): ?>
        <div class="card">
            <h2><?= htmlspecialchars($animal['username']) ?></h2>
            <p>Email: <?= htmlspecialchars($animal['email']) ?></p>
            <form method="POST" class="actions">
                <input type="hidden" name="animal_id" value="0"><!-- Not used for shelter swipes -->
                <input type="hidden" name="user_id" value="<?= $animal['id'] ?>">
                <button type="submit" name="direction" value="left" class="btn dislike">Dislike</button>
                <button type="submit" name="direction" value="right" class="btn like">Like</button>
            </form>
        </div>
    <?php else: ?>
        <p>No more matches to show right now. Check back later!</p>
        <a href="dashboard.php">Back to Dashboard</a>
    <?php endif; ?>
</body>
</html>