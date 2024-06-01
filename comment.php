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
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $post_id, $user_id, $content);

    if ($stmt->execute()) {
        header("Location: view_post.php?id=$post_id");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-dark text-white">
            <h3 class="mb-0">Add Comment</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                <div class="form-group">
                    <label for="content">Comment:</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
