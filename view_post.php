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

    $is_admin_or_mod = false;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
        $sql = "SELECT is_admin, is_moderator FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $is_admin_or_mod = $user['is_admin'] || $user['is_moderator'];
    }

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

    if (isset($_GET['action']) && $_GET['action'] === 'copy') {
        $currentURL = urlencode('http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        echo "<textarea id='copyText' style='opacity:0;position:fixed;top:0;left:0;'>{$currentURL}</textarea>";
        echo "<script>document.getElementById('copyText').select();document.execCommand('copy');window.location.href='{$_SERVER['HTTP_REFERER']}';</script>";
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
                <p>By: <a href="user_page.php?username=<?php echo htmlspecialchars($post['username']); ?>"><?php echo htmlspecialchars($post['username']); ?></a></p>
                <p>Posted on: <span id="postCreatedAt"></span></p>
                <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
                <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>

                <div class="float-right">
                    <div class="dropdown">
                        <button class="btn btn-secondary" type="button" id="shareDropdown" aria-haspopup="true" aria-expanded="false">
                            Share <i class="bi bi-chevron-down"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="shareDropdown">
                            <a class="dropdown-item" href="#" id="copyToClipboard"><i class="bi bi-clipboard"></i> Copy to Clipboard</a>
                            <a class="dropdown-item" href="coming_soon.php"><i class="bi bi-facebook"></i> Facebook</a>
                            <a class="dropdown-item" href="coming_soon.php"><i class="bi bi-twitter"></i> Twitter</a>
                            <a class="dropdown-item" href="coming_soon.php"><i class="bi bi-envelope"></i> Gmail</a>
                        </div>
                    </div>
                </div>
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
                    <div class="form-group">
                        <label for="comment_image">Upload Image:</label>
                        <input type="file" id="comment_image" name="comment_image" class="form-control">
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
                            $comment_image_path = "uploads/comments/" . $comment['id'] . "/";
                            if (is_dir($comment_image_path)) {
                                $comment_images = glob($comment_image_path . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                                if (count($comment_images) > 0) {
                                    echo '<img src="' . $comment_images[0] . '" alt="Comment Image" class="img-fluid mb-3">';
                                }
                            }
                            ?>
                            <p class="card-text"><?php echo nl2br(htmlspecialchars_decode($comment['content'])); ?></p>
                            <p class="card-text"><small class="text-muted">By: <a href="user_page.php?username=<?php echo htmlspecialchars($comment['username']); ?>"><?php echo htmlspecialchars($comment['username']); ?></a> | <span class="commentCreatedAt" data-timestamp="<?php echo $comment['created_at']; ?>"></span></small></p>
                            <?php if ($is_admin_or_mod): ?>
                                <a href="?id=<?php echo $post_id; ?>&delete_comment=<?php echo $comment['id']; ?>" class="btn btn-danger btn-sm float-right" onclick="return confirm('Are you sure you want to delete this comment?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

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

            var shareDropdown = document.getElementById('shareDropdown');
            var dropdownMenu = shareDropdown.nextElementSibling;

            shareDropdown.addEventListener('click', function () {
                dropdownMenu.classList.toggle('show');
            });

            document.addEventListener('click', function (event) {
                if (!shareDropdown.contains(event.target) && !dropdownMenu.contains(event.target)) {
                    dropdownMenu.classList.remove('show');
                }
            });

            document.getElementById('copyToClipboard').addEventListener('click', function (event) {
                event.preventDefault();
                var copyText = 'http://' + window.location.host + window.location.pathname;
                
                var textarea = document.createElement('textarea');
                textarea.value = copyText;
                textarea.style.position = 'fixed';
                textarea.style.opacity = 0;
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                document.body.removeChild(textarea);
                alert('Link copied to clipboard!');
            });
        });
    </script>
</body>
</html>
