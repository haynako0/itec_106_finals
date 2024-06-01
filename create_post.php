<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Post</title>
    <link rel="icon" href="img/white_girl_save_me.ico">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
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
            $post_id = $stmt->insert_id;

            if (!empty($_FILES['image']['name'])) {
                $target_dir = "uploads/$post_id/";
                if (!file_exists($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                $target_file = $target_dir . basename($_FILES["image"]["name"]);
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
 
                } else {
                    echo "Error uploading image.";
                }
            }

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

    <div class="container mt-5 mb-5">
        <h1 id="txt3">Create Post</h1>

        <form id="create_post" method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label class="text-light" for="title">Title:</label>
                <input type="text" id="title" name="title" class="form-control" required maxlength="100">
                <small id="titleCount" class="form-text text-muted">0/100</small>
            </div>
            <div class="form-group">
                <label class="text-light" for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" rows="5" required></textarea>
            </div>
            <div class="form-group">
                <label class="text-light" for="image">Upload Image:</label>
                <input type="file" id="image" name="image" class="form-control">
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

    <script>
        CKEDITOR.replace('content', {
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'styles', items: ['Format', 'FontSize'] }
            ],
            removeButtons: 'Cut,Copy,Paste,Undo,Redo,Anchor,Image,Table,HorizontalRule,SpecialChar,Blockquote,CreateDiv,Maximize,Source,Scayt,Superscript,Subscript'
        });

        $('#title').on('input', function() {
            var titleLength = $(this).val().length;
            $('#titleCount').text(titleLength + '/100');
        });
    </script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
