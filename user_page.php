<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
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

    if (isset($_GET['username'])) {
        $username_param = $conn->real_escape_string($_GET['username']);

        $sql_user = "SELECT * FROM users WHERE username = '$username_param'";
        $result_user = $conn->query($sql_user);

        if ($result_user->num_rows > 0) {
            $user = $result_user->fetch_assoc();
            $user_id = $user['id'];
            $username = $user['username'];

            $sql_posts = "SELECT * FROM posts WHERE username = '$username' ORDER BY created_at DESC";
            $result_posts = $conn->query($sql_posts);

            if ($result_posts === false) {
                echo "Error fetching posts: " . $conn->error;
                exit;
            }
        } else {
            echo "User not found.";
            exit;
        }
    } else {
        echo "Username parameter not set.";
        exit;
    }
    ?>

    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <h2 id="txt" class="text-center mb-4"><?php echo htmlspecialchars($username); ?>'s Profile</h2>
                <div class="card mb-4">
                    <div class="card-body">
                        <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
                    </div>
                </div>
                <h3 id="txt" class="text-center mb-4">Posts by <?php echo htmlspecialchars($username); ?></h3>
                <?php if ($result_posts->num_rows > 0): ?>
                    <?php while ($post = $result_posts->fetch_assoc()): ?>
                        <div id="content_card" class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <?php
                                $image_path = "uploads/" . $post['id'] . "/";
                                if (is_dir($image_path)) {
                                    $images = glob($image_path . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
                                    if (count($images) > 0) {
                                        echo '<img src="' . $images[0] . '" alt="Post Image" class="img-fluid mb-3">';
                                    }
                                }
                                ?>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars_decode($post['content'])); ?></p>
                                <p>Posted on: <span class="postCreatedAt" data-timestamp="<?php echo $post['created_at']; ?>"></span></p>
                                <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
                                <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" class="btn btn-primary">View Post</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p class="text-center">No posts found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <br>

    <?php include 'templates/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var postCreatedTimes = document.getElementsByClassName('postCreatedAt');
            for (var i = 0; i < postCreatedTimes.length; i++) {
                var postCreatedAt = postCreatedTimes[i].getAttribute('data-timestamp');
                var postDate = new Date(postCreatedAt);
                postCreatedTimes[i].textContent = postDate.toLocaleString();
            }
        });
    </script>
</body>
</html>
