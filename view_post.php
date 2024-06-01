<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gacha Garden</title>
    <link rel="icon" href="img/white_girl_save_me.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
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
            <div id="post_header" class="card-header">
                <h2 id="txt8" class="mb-0"><?php echo htmlspecialchars($post['title']); ?></h2>
            </div>
            <div id="post_body" class="card-body">
                <?php
                $image_path = "uploads/" . $post['id'] . "/";
                if (is_dir($image_path)) {
                    $images = glob($image_path . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                    if (count($images) > 0) {
                        echo '<img src="' . $images[0] . '" alt="Post Image" class="img-fluid mb-3">';
                    }
                }
                ?>
                <p><?php echo nl2br(htmlspecialchars_decode($post['content'])); ?></p>
                <p>By: <?php echo htmlspecialchars($post['username']); ?></p>
                <p>Posted on: <span id="postCreatedAt"></span></p>
                <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
                <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>
            </div>
        </div>

        <div class="card mt-3 mb-5">
            <div id="comment_header" class="card-header">
                <h3 id="txt8" class="mb-0">Comments</h3>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="comment.php" style="margin-bottom: 20px;" enctype="multipart/form-data">
                        <div class="form-group">
                            <textarea class="form-control" name="content" rows="3" required></textarea>
                        </div>
                        <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post['id']); ?>">
                        <button type="submit" class="btn btn-primary">Comment</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Log in</a> to post a comment.</p>
                <?php endif; ?>

                <?php while($comment = $result_comments->fetch_assoc()): ?>
                    <div class="card mb-3">
                        <div id="comment_body_<?php echo $comment['id']; ?>" class="card-body">
                            <?php
                            if (!empty($comment['image'])) {
                                echo '<img src="' . htmlspecialchars($comment['image']) . '" alt="Comment Image" class="img-fluid mb-3">';
                            }
                            ?>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars_decode($comment['content'])); ?></p>
                            <p class="card-text"><small class="text-muted">By: <?php echo htmlspecialchars($comment['username']); ?> | <span class="commentCreatedAt" data-timestamp="<?php echo $comment['created_at']; ?>"></span></small></p>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var postCreatedAt = '<?php echo $post['created_at']; ?>';
            var postDate = new Date(postCreatedAt);
            document.getElementById('postCreatedAt').textContent = postDate.toLocaleString();

            var commentCreatedTimes = document.getElementsByClassName('commentCreatedAt');
            for (var i = 0; i < commentCreatedTimes.length; i++) {
                var commentCreatedAt = commentCreatedTimes[i].getAttribute('data-timestamp');
                var commentDate = new Date(commentCreatedAt);
                commentCreatedTimes[i].textContent = commentDate.toLocaleString();
            }
        });
    </script>
</body>
</html>
