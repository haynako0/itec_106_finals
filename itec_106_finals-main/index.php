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

    $sql = "SELECT * FROM posts ORDER BY created_at DESC";
    $result = $conn->query($sql);
    ?>

    <div class="row">
        <?php while($post = $result->fetch_assoc()): ?>
            <div class="col-md-6 mt-4 mb-3">
                <div class="card">
                    <div id="content_card" class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($post['title']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">By: <?php echo htmlspecialchars($post['username']); ?></h6>
                        <p class="card-text"><?php echo nl2br(htmlspecialchars($post['content'])); ?></p>
                        <p><span class="badge badge-info"><?php echo htmlspecialchars($post['game_flair']); ?></span></p>
                        <p><span class="badge badge-secondary"><?php echo htmlspecialchars($post['post_flair']); ?></span></p>
                        <a href="view_post.php?id=<?php echo $post['id']; ?>" id="view_btn" class="btn">View Post</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>