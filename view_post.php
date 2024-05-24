<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT * FROM posts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $post_id);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();

if (!$post) {
    echo "Post not found.";
    include 'templates/footer.php';
    exit;
}

$sql_comments = "SELECT comments.*, users.username 
                 FROM comments 
                 JOIN users ON comments.user_id = users.id 
                 WHERE post_id = ? 
                 ORDER BY created_at DESC";
$stmt_comments = $conn->prepare($sql_comments);
$stmt_comments->bind_param('i', $post_id);
$stmt_comments->execute();
$result_comments = $stmt_comments->get_result();
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h2 class="mb-0"><?php echo htmlspecialchars($post['title']); ?></h2>
        </div>
        <div class="card-body">
            <p><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
            <p>By: <?php echo htmlspecialchars($post['username']); ?></p>
            <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
            <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>
            <p>Votes: <?php echo htmlspecialchars($post['votes']); ?></p>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="comment.php">
                    <div class="form-group">
                        <textarea class="form-control" name="content" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                    <button type="submit" class="btn btn-primary">Comment</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Log in</a> to post a comment.</p>
            <?php endif; ?>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">Comments</h3>
        </div>
        <div class="card-body">
            <?php while($comment = $result_comments->fetch_assoc()): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                        <p class="card-text"><small class="text-muted">By: <?php echo htmlspecialchars($comment['username']); ?></small></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
