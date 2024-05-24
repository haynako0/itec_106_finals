<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'moderator')) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $item_type = $_POST['item_type'];
    $action = $_POST['action'];

    if ($action == 'delete') {
        switch ($item_type) {
            case 'game_flair':
                deleteFlair($conn, $item_id, 'game_flairs');
                break;
            case 'post_flair':
                deleteFlair($conn, $item_id, 'post_flairs');
                break;
            case 'post':
                deletePost($conn, $item_id);
                break;
            case 'comment':
                deleteComment($conn, $item_id);
                break;
            case 'user':
                if (canDeleteUser($conn, $item_id)) {
                    deleteUser($conn, $item_id);
                }
                break;
        }
        header("Location: moderator_panel.php");
        exit;
    }
}

function deleteFlair($conn, $flair_id, $table) {
    $flair_type = ($table == 'game_flairs') ? 'game' : 'post';

    $sql_archive_flair = "INSERT INTO deleted_flairs (name, type) SELECT name, ? FROM $table WHERE id = ?";
    $stmt_archive_flair = $conn->prepare($sql_archive_flair);
    $stmt_archive_flair->bind_param("si", $flair_type, $flair_id);
    $stmt_archive_flair->execute();

    $sql_posts = "SELECT id FROM posts WHERE {$flair_type}_flair = ?";
    $stmt_posts = $conn->prepare($sql_posts);
    $stmt_posts->bind_param("s", $flair_id);
    $stmt_posts->execute();
    $result_posts = $stmt_posts->get_result();

    while ($post = $result_posts->fetch_assoc()) {
        deletePost($conn, $post['id']);
    }

    $sql_delete_flair = "DELETE FROM $table WHERE id = ?";
    $stmt_delete_flair = $conn->prepare($sql_delete_flair);
    $stmt_delete_flair->bind_param("i", $flair_id);
    $stmt_delete_flair->execute();
}

function deletePost($conn, $post_id) {
    $sql_archive_post = "INSERT INTO deleted_posts (id, title, content) SELECT id, title, content FROM posts WHERE id = ?";
    $stmt_archive_post = $conn->prepare($sql_archive_post);
    $stmt_archive_post->bind_param("i", $post_id);
    $stmt_archive_post->execute();

    $sql_archive_comments = "INSERT INTO deleted_comments (id, content) SELECT id, content FROM comments WHERE post_id = ?";
    $stmt_archive_comments = $conn->prepare($sql_archive_comments);
    $stmt_archive_comments->bind_param("i", $post_id);
    $stmt_archive_comments->execute();

    $sql_delete_comments = "DELETE FROM comments WHERE post_id = ?";
    $stmt_delete_comments = $conn->prepare($sql_delete_comments);
    $stmt_delete_comments->bind_param("i", $post_id);
    $stmt_delete_comments->execute();

    $sql_delete_post = "DELETE FROM posts WHERE id = ?";
    $stmt_delete_post = $conn->prepare($sql_delete_post);
    $stmt_delete_post->bind_param("i", $post_id);
    $stmt_delete_post->execute();
}

function deleteComment($conn, $comment_id) {
    $sql_archive_comment = "INSERT INTO deleted_comments (id, content) SELECT id, content FROM comments WHERE id = ?";
    $stmt_archive_comment = $conn->prepare($sql_archive_comment);
    $stmt_archive_comment->bind_param("i", $comment_id);
    $stmt_archive_comment->execute();

    $sql_delete_comment = "DELETE FROM comments WHERE id = ?";
    $stmt_delete_comment = $conn->prepare($sql_delete_comment);
    $stmt_delete_comment->bind_param("i", $comment_id);
    $stmt_delete_comment->execute();
}

function deleteUser($conn, $user_id) {
    $sql_archive_user = "INSERT INTO deleted_users (id, username, password, is_admin, is_moderator)
                         SELECT id, username, password, is_admin, is_moderator FROM users WHERE id = ?";
    $stmt_archive_user = $conn->prepare($sql_archive_user);
    $stmt_archive_user->bind_param("i", $user_id);
    $stmt_archive_user->execute();

    $sql_archive_posts = "INSERT INTO deleted_posts (id, title, content)
                         SELECT id, title, content FROM posts WHERE user_id = ?";
    $stmt_archive_posts = $conn->prepare($sql_archive_posts);
    $stmt_archive_posts->bind_param("i", $user_id);
    $stmt_archive_posts->execute();

    $sql_archive_comments = "INSERT INTO deleted_comments (id, content)
                            SELECT id, content FROM comments WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?)";
    $stmt_archive_comments = $conn->prepare($sql_archive_comments);
    $stmt_archive_comments->bind_param("i", $user_id);
    $stmt_archive_comments->execute();

    $sql_delete_comments = "DELETE FROM comments WHERE post_id IN (SELECT id FROM posts WHERE user_id = ?)";
    $stmt_delete_comments = $conn->prepare($sql_delete_comments);
    $stmt_delete_comments->bind_param("i", $user_id);
    $stmt_delete_comments->execute();

    $sql_delete_posts = "DELETE FROM posts WHERE user_id = ?";
    $stmt_delete_posts = $conn->prepare($sql_delete_posts);
    $stmt_delete_posts->bind_param("i", $user_id);
    $stmt_delete_posts->execute();

    $sql_delete_user = "DELETE FROM users WHERE id = ?";
    $stmt_delete_user = $conn->prepare($sql_delete_user);
    $stmt_delete_user->bind_param("i", $user_id);
    $stmt_delete_user->execute();
}

function canDeleteUser($conn, $user_id) {
    global $conn;
    $sql = "SELECT is_admin, is_moderator FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if (!$user) return false;

    if ($_SESSION['role'] == 'moderator') {
        return !$user['is_admin'] && !$user['is_moderator'] && $user_id != $_SESSION['user_id'];
    }

    if ($_SESSION['role'] == 'admin') {
        return !$user['is_admin'] && $user_id != $_SESSION['user_id'];
    }

    return false;
}

$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);

$sql_game_flairs = "SELECT * FROM game_flairs";
$result_game_flairs = $conn->query($sql_game_flairs);

$sql_post_flairs = "SELECT * FROM post_flairs";
$result_post_flairs = $conn->query($sql_post_flairs);

$sql_posts = "SELECT * FROM posts";
$result_posts = $conn->query($sql_posts);

$sql_comments = "SELECT * FROM comments";
$result_comments = $conn->query($sql_comments);
?>

<div class="container mt-5">
    <h2>Moderator Panel</h2>

    <nav class="mt-3 mb-3">
        <a href="deletions_archive.php" class="btn btn-primary">View Deletions Archive</a>
    </nav>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Users
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search by Username" name="user_search" value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result_users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>
                                <?php
                                if ($user['is_admin']) {
                                    echo "Administrator";
                                } elseif ($user['is_moderator']) {
                                    echo "Moderator";
                                } else {
                                    echo "User";
                                }
                                ?>
                            </td>
                            <td>
                                <?php if (
                                    ($_SESSION['role'] == 'admin' && !$user['is_admin'] && $user['id'] != $_SESSION['user_id']) ||
                                    ($_SESSION['role'] == 'moderator' && !$user['is_admin'] && !$user['is_moderator'] && $user['id'] != $_SESSION['user_id'])
                                ): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="item_id" value="<?php echo $user['id']; ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="item_type" value="user">
                                        <button type="submit" class="btn btn-danger btn-sm">Delete User</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Game Flairs
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search by Name" name="game_flair_search" value="<?php echo isset($_GET['game_flair_search']) ? htmlspecialchars($_GET['game_flair_search']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($flair = $result_game_flairs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flair['name']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" value="<?php echo $flair['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="item_type" value="game_flair">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Game Flair</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Post Flairs
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search by Name" name="post_flair_search" value="<?php echo isset($_GET['post_flair_search']) ? htmlspecialchars($_GET['post_flair_search']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($flair = $result_post_flairs->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($flair['name']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" value="<?php echo $flair['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="item_type" value="post_flair">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Post Flair</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Posts
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search by Title" name="post_search" value="<?php echo isset($_GET['post_search']) ? htmlspecialchars($_GET['post_search']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($post = $result_posts->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($post['title']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" value="<?php echo $post['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="item_type" value="post">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Post</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Comments
        </div>
        <div class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" class="form-control" placeholder="Search by Content" name="comment_search" value="<?php echo isset($_GET['comment_search']) ? htmlspecialchars($_GET['comment_search']) : ''; ?>">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </div>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Content</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($comment = $result_comments->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comment['content']); ?></td>
                            <td>
                                <form method="POST" action="">
                                    <input type="hidden" name="item_id" value="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="item_type" value="comment">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete Comment</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<?php include 'templates/footer.php'; ?>
