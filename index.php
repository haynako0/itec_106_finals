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

    $sql = "
        SELECT * FROM posts
        ORDER BY 
            FIELD(game_flair, 'MODERATOR ANNOUNCEMENT', 'ADMINISTRATOR ANNOUNCEMENT') DESC,
            created_at DESC
    ";
    $result = $conn->query($sql);
    ?>

    <div class="container mt-5">
        <div id="announcementCarousel" class="carousel slide mb-5" data-ride="carousel">
            <ol class="carousel-indicators">
                <?php
                $announcement_count = 0;
                while($post = $result->fetch_assoc()):
                    $is_announcement = in_array($post['game_flair'], ['MODERATOR ANNOUNCEMENT', 'ADMINISTRATOR ANNOUNCEMENT']);
                    if ($is_announcement):
                ?>
                        <li data-target="#announcementCarousel" data-slide-to="<?php echo $announcement_count; ?>" class="<?php echo $announcement_count == 0 ? 'active' : ''; ?>"></li>
                <?php
                        $announcement_count++;
                    endif;
                endwhile;
                ?>
            </ol>
            <div class="carousel-inner">
                <?php
                $result->data_seek(0);
                $is_first_announcement = true;
                while($post = $result->fetch_assoc()):
                    $is_announcement = in_array($post['game_flair'], ['MODERATOR ANNOUNCEMENT', 'ADMINISTRATOR ANNOUNCEMENT']);
                    if ($is_announcement):
                ?>
                        <div class="carousel-item <?php echo $is_first_announcement ? 'active' : ''; ?>">
                            <div class="card announcement-card">
                                <div id="content_card" class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                    <h6 class="card-subtitle mb-2 text-muted">By: 
                                        <a href="user_page.php?username=<?php echo urlencode($post['username']); ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($post['username']); ?>
                                        </a>
                                    </h6>
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
                                    <a href="view_post.php?id=<?php echo $post['id']; ?>" id="view_btn" class="btn btn-primary">View Post</a>
                                </div>
                            </div>
                        </div>
                <?php
                        $is_first_announcement = false;
                    endif;
                endwhile;
                ?>
            </div>
        </div>

        <div class="row">
            <?php
            $result->data_seek(0);
            while($post = $result->fetch_assoc()):
                $is_announcement = in_array($post['game_flair'], ['MODERATOR ANNOUNCEMENT', 'ADMINISTRATOR ANNOUNCEMENT']);
                if (!$is_announcement):
            ?>
                    <div class="col-md-6 mt-4 mb-3">
                        <div class="card">
                            <div id="content_card" class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                                <h6 class="card-subtitle mb-2 text-muted">By: 
                                    <a href="user_page.php?username=<?php echo urlencode($post['username']); ?>" class="text-decoration-none text-dark">
                                        <?php echo htmlspecialchars($post['username']); ?>
                                    </a>
                                </h6>
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
                                <a href="view_post.php?id=<?php echo $post['id']; ?>" id="view_btn" class="btn btn-primary">View Post</a>
                            </div>
                        </div>
                    </div>
            <?php
                endif;
            endwhile;
            ?>
        </div>

        <a class="carousel-control-prev" href="#announcementCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#announcementCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <?php include 'templates/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

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
