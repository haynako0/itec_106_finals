<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql_user = "SELECT is_admin, is_moderator FROM users WHERE id = ?";
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();

if ($result_user->num_rows == 1) {
    $user = $result_user->fetch_assoc();

    if (!$user['is_admin'] && !$user['is_moderator']) {
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $flair_type = $_POST['flair_type'];
    $flair_name = $_POST['flair_name'];

    $sql = "";

    if ($flair_type == 'game') {
        $sql = "INSERT INTO game_flairs (name) VALUES (?)";
    } else {
        $sql = "INSERT INTO post_flairs (name) VALUES (?)";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $flair_name);

    if ($stmt->execute()) {
        header("Location: manage_flairs.php");
    } else {
        echo "Error: " . $sql . "<br>" . $stmt->error;
    }
}

$sql_game_flairs = "SELECT * FROM game_flairs";
$result_game_flairs = $conn->query($sql_game_flairs);

$sql_post_flairs = "SELECT * FROM post_flairs";
$result_post_flairs = $conn->query($sql_post_flairs);
?>

<div class="container mt-5">
    <h2>Manage Flairs</h2>

    <form method="POST" action="">
        <div class="form-group">
            <label for="flair_type">Flair Type:</label>
            <select id="flair_type" name="flair_type" class="form-control" required>
                <option value="game">Game Flair</option>
                <option value="post">Post Flair</option>
            </select>
        </div>
        <div class="form-group">
            <label for="flair_name">Flair Name:</label>
            <input type="text" id="flair_name" name="flair_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Flair</button>
    </form>

    <div class="mt-4">
        <h3>Existing Game Flairs</h3>
        <ul class="list-group">
            <?php while($row = $result_game_flairs->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($row['name']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="mt-4">
        <h3>Existing Post Flairs</h3>
        <ul class="list-group">
            <?php while($row = $result_post_flairs->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($row['name']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
