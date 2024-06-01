<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $content = $_POST['content'];

    // Insert comment into database
    $sql = "INSERT INTO comments (post_id, user_id, content) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iis', $post_id, $user_id, $content);

    if ($stmt->execute()) {
        $comment_id = $stmt->insert_id;

        // Upload image if provided
        if (!empty($_FILES['comment_image']['name'])) {
            $target_dir = "uploads/comments/$comment_id/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_file = $target_dir . basename($_FILES["comment_image"]["name"]);

            // Check file type
            $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            $allowed_extensions = array("jpg", "jpeg", "png", "gif");

            if (!in_array($imageFileType, $allowed_extensions)) {
                echo "Error: Only JPG, JPEG, PNG, and GIF files are allowed.";
            } else {
                // Upload file
                if (move_uploaded_file($_FILES["comment_image"]["tmp_name"], $target_file)) {
                    // File uploaded successfully
                } else {
                    echo "Error uploading comment image.";
                }
            }
        }

        // Redirect to view post page
        header("Location: view_post.php?id=$post_id");
    } else {
        // Error in SQL execution
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
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($_GET['id']); ?>">
                <div class="form-group">
                    <label for="content">Comment:</label>
                    <textarea class="form-control" id="content" name="content" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="comment_image">Upload Image:</label>
                    <input type="file" id="comment_image" name="comment_image" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>