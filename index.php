<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

$sql = "SELECT * FROM posts ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<div class="row">
    <?php while($post = $result->fetch_assoc()): ?>
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                    <h6 class="card-subtitle mb-2 text-muted">By: <?php echo htmlspecialchars($post['username']); ?></h6>
                    <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                    <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
                    <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>
                    <p>Votes: <?php echo htmlspecialchars($post['votes']); ?></p>
                    <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">View Post</a>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php include 'templates/footer.php'; ?>
