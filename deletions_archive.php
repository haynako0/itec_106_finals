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
$user = $result_user->fetch_assoc();

if (!$user['is_admin'] && !$user['is_moderator']) {
    header("Location: login.php");
    exit;
}

$sql_deleted_flairs = "SELECT * FROM deleted_flairs";
$result_deleted_flairs = $conn->query($sql_deleted_flairs);

$sql_deleted_posts = "SELECT * FROM deleted_posts";
$result_deleted_posts = $conn->query($sql_deleted_posts);

$sql_deleted_comments = "SELECT * FROM deleted_comments";
$result_deleted_comments = $conn->query($sql_deleted_comments);

$sql_deleted_users = "SELECT * FROM deleted_users";
$result_deleted_users = $conn->query($sql_deleted_users);
?>

<div class="container mt-5">
    <h2>Deletions Archive</h2>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            Deleted Flairs
        </div>
        <ul class="list-group list-group-flush">
            <?php while ($flair = $result_deleted_flairs->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($flair['name']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            Deleted Posts
        </div>
        <ul class="list-group list-group-flush">
            <?php while ($post = $result_deleted_posts->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($post['title']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            Deleted Comments
        </div>
        <ul class="list-group list-group-flush">
            <?php while ($comment = $result_deleted_comments->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($comment['content']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            Deleted Users
        </div>
        <ul class="list-group list-group-flush">
            <?php while ($user = $result_deleted_users->fetch_assoc()): ?>
                <li class="list-group-item"><?php echo htmlspecialchars($user['username']); ?></li>
            <?php endwhile; ?>
        </ul>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
