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

    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $user_id = $_SESSION['user_id'];
        $title = $_POST['title'];
        $content = $_POST['content'];
        $game_flair_name = $_POST['game_flair_name'];
        $post_flair_name = $_POST['post_flair_name'];

        $sql_username = "SELECT username FROM users WHERE id = ?";
        $stmt_username = $conn->prepare($sql_username);
        $stmt_username->bind_param("i", $user_id);
        $stmt_username->execute();
        $result_username = $stmt_username->get_result();
        $user = $result_username->fetch_assoc();
        $username = $user['username'];

        $votes = 0;

        $sql = "INSERT INTO posts (title, content, game_flair, post_flair, username, votes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssi", $title, $content, $game_flair_name, $post_flair_name, $username, $votes);
        
        if ($stmt->execute()) {
            header("Location: index.php");
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
        <h2 id="txt3">Create Post</h2>

        <form id="create_post" method="POST" action="">
            <div class="form-group">
                <label class="text-light" for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="text-light" for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label class="text-light" for="game_flair_name">Game Flair:</label>
                <select id="game_flair_name" name="game_flair_name" class="form-control" required>
                    <?php while($row = $result_game_flairs->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['name']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="text-light" for="post_flair_name">Post Flair:</label>
                <select id="post_flair_name" name="post_flair_name" class="form-control" required>
                    <?php while($row = $result_post_flairs->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['name']); ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" id="create_btn" class="btn"><span><i class="bi bi-check-circle-fill mr-2"></i></span>Create Post</button>
        </form>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>