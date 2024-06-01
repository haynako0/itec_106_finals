<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderation Panel</title>
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
                case 'user':
                    if (canDeleteUser($conn, $item_id)) {
                        deleteUser($conn, $item_id);
                    }
                    break;
                case 'post':
                    deletePost($conn, $item_id);
                    break;
            }
            header("Location: moderator_panel.php");
            exit;
        } elseif ($action == 'promote') {
            $sql = "UPDATE users SET is_moderator = 1 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            if ($stmt->execute()) {
                header("Location: moderator_panel.php");
            } else {
                echo "Error: " . $stmt->error;
            }
        } elseif ($action == 'demote') {
            $sql = "UPDATE users SET is_moderator = 0 WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $item_id);
            if ($stmt->execute()) {
                header("Location: moderator_panel.php");
            } else {
                echo "Error: " . $stmt->error;
            }
        } elseif ($action == 'add_flair') {
            $flair_type = $_POST['flair_type'];
            $flair_name = $_POST['flair_name'];
            $sql = "";
            if ($flair_type == 'game') {
                $sql = "INSERT INTO game_flairs (name) VALUES (?)";
            } else {
                $sql = "INSERT INTO post_flairs (name) VALUES (?)";
            }
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $flair_name);
            if ($stmt->execute()) {
                header("Location: moderator_panel.php");
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }
    }

    function deleteFlair($conn, $flair_id, $table)
    {
        $flair_column = ($table == 'game_flairs') ? 'game_flair' : 'post_flair';

        $sql_fetch_flair_name = "SELECT name FROM $table WHERE id = ?";
        $stmt_fetch_flair_name = $conn->prepare($sql_fetch_flair_name);
        $stmt_fetch_flair_name->bind_param("i", $flair_id);
        $stmt_fetch_flair_name->execute();
        $result_fetch_flair_name = $stmt_fetch_flair_name->get_result();
        $flair_name = $result_fetch_flair_name->fetch_assoc()['name'];
        $stmt_fetch_flair_name->close();

        $sql_count = "SELECT COUNT(*) AS count FROM $table";
        $result_count = $conn->query($sql_count);
        $count = $result_count->fetch_assoc()['count'];

        if ($count > 1 && $flair_name) {
            $sql_delete_posts = "DELETE FROM posts WHERE $flair_column = ?";
            $stmt_delete_posts = $conn->prepare($sql_delete_posts);
            $stmt_delete_posts->bind_param("s", $flair_name);
            $stmt_delete_posts->execute();
            $stmt_delete_posts->close();

            $sql_delete_flair = "DELETE FROM $table WHERE id = ?";
            $stmt_delete_flair = $conn->prepare($sql_delete_flair);
            $stmt_delete_flair->bind_param("i", $flair_id);
            $stmt_delete_flair->execute();
            $stmt_delete_flair->close();
        }
    }

    function deleteUser($conn, $user_id)
    {
        $sql_delete_comments = "DELETE FROM comments WHERE user_id = ?";
        $stmt_delete_comments = $conn->prepare($sql_delete_comments);
        $stmt_delete_comments->bind_param("i", $user_id);
        $stmt_delete_comments->execute();
        $stmt_delete_comments->close();

        $sql_get_username = "SELECT username FROM users WHERE id = ?";
        $stmt_get_username = $conn->prepare($sql_get_username);
        $stmt_get_username->bind_param("i", $user_id);
        $stmt_get_username->execute();
        $result_get_username = $stmt_get_username->get_result();
        $username = $result_get_username->fetch_assoc()['username'];
        $stmt_get_username->close();

        $sql_delete_posts = "DELETE FROM posts WHERE username = ?";
        $stmt_delete_posts = $conn->prepare($sql_delete_posts);
        $stmt_delete_posts->bind_param("s", $username);
        $stmt_delete_posts->execute();
        $stmt_delete_posts->close();

        $sql_delete_user = "DELETE FROM users WHERE id = ?";
        $stmt_delete_user = $conn->prepare($sql_delete_user);
        $stmt_delete_user->bind_param("i", $user_id);
        $stmt_delete_user->execute();
        $stmt_delete_user->close();
    }

    function deletePost($conn, $post_id)
    {
        $sql_delete_comments = "DELETE FROM comments WHERE post_id = ?";
        $stmt_delete_comments = $conn->prepare($sql_delete_comments);
        $stmt_delete_comments->bind_param("i", $post_id);
        $stmt_delete_comments->execute();
        $stmt_delete_comments->close();

        $sql_delete_post = "DELETE FROM posts WHERE id = ?";
        $stmt_delete_post = $conn->prepare($sql_delete_post);
        $stmt_delete_post->bind_param("i", $post_id);
        $stmt_delete_post->execute();
        $stmt_delete_post->close();
    }

    function canDeleteUser($conn, $user_id)
    {
        $sql = "SELECT is_admin FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if (!$user) return false;

        if ($_SESSION['role'] == 'moderator') {
            return !$user['is_admin'];
        }

        if ($_SESSION['role'] == 'admin') {
            return $user_id != $_SESSION['user_id'];
        }

        return false;
    }

    $entriesPerPage = 10;

    $sql_count_users = "SELECT COUNT(*) AS total FROM users";
    $result_count_users = $conn->query($sql_count_users);
    $total_users = $result_count_users->fetch_assoc()['total'];

    $sql_count_posts = "SELECT COUNT(*) AS total FROM posts";
    $result_count_posts = $conn->query($sql_count_posts);
    $total_posts = $result_count_posts->fetch_assoc()['total'];

    $sql_count_game_flairs = "SELECT COUNT(*) AS total FROM game_flairs";
    $result_count_game_flairs = $conn->query($sql_count_game_flairs);
    $total_game_flairs = $result_count_game_flairs->fetch_assoc()['total'];

    $sql_count_post_flairs = "SELECT COUNT(*) AS total FROM post_flairs";
    $result_count_post_flairs = $conn->query($sql_count_post_flairs);
    $total_post_flairs = $result_count_post_flairs->fetch_assoc()['total'];

    $total_pages_users = ceil($total_users / $entriesPerPage);
    $total_pages_posts = ceil($total_posts / $entriesPerPage);
    $total_pages_game_flairs = ceil($total_game_flairs / $entriesPerPage);
    $total_pages_post_flairs = ceil($total_post_flairs / $entriesPerPage);

    $current_page_users = isset($_GET['page_users']) ? $_GET['page_users'] : 1;
    $current_page_posts = isset($_GET['page_posts']) ? $_GET['page_posts'] : 1;
    $current_page_game_flairs = isset($_GET['page_game_flairs']) ? $_GET['page_game_flairs'] : 1;
    $current_page_post_flairs = isset($_GET['page_post_flairs']) ? $_GET['page_post_flairs'] : 1;

    $offset_users = ($current_page_users - 1) * $entriesPerPage;
    $offset_posts = ($current_page_posts - 1) * $entriesPerPage;
    $offset_game_flairs = ($current_page_game_flairs - 1) * $entriesPerPage;
    $offset_post_flairs = ($current_page_post_flairs - 1) * $entriesPerPage;

    $search_users = isset($_GET['search_users']) ? $_GET['search_users'] : '';
    $search_posts = isset($_GET['search_posts']) ? $_GET['search_posts'] : '';
    $search_game_flairs = isset($_GET['search_game_flairs']) ? $_GET['search_game_flairs'] : '';
    $search_post_flairs = isset($_GET['search_post_flairs']) ? $_GET['search_post_flairs'] : '';

    $sql_users = "SELECT * FROM users 
                WHERE username LIKE '%$search_users%'
                LIMIT $entriesPerPage OFFSET $offset_users";
    $result_users = $conn->query($sql_users);

    $sql_posts = "SELECT p.id, p.title, p.content, p.game_flair, p.post_flair, p.created_at, u.username
                FROM posts p
                JOIN users u ON p.username = u.username
                WHERE p.title LIKE '%$search_posts%' OR p.content LIKE '%$search_posts%'
                LIMIT $entriesPerPage OFFSET $offset_posts";
    $result_posts = $conn->query($sql_posts);

    $sql_game_flairs = "SELECT * FROM game_flairs
                        WHERE name LIKE '%$search_game_flairs%'
                        LIMIT $entriesPerPage OFFSET $offset_game_flairs";
    $result_game_flairs = $conn->query($sql_game_flairs);

    $sql_post_flairs = "SELECT * FROM post_flairs
                        WHERE name LIKE '%$search_post_flairs%'
                        LIMIT $entriesPerPage OFFSET $offset_post_flairs";
    $result_post_flairs = $conn->query($sql_post_flairs);
    ?>

    <div class="container mt-5 mb-5">
        <h1 id="txt7" class="mb-3">Moderation Panel</h1>

        <div class="card mb-3">
            <div id="post_header" class="card-header">
                <h2 id="txt8">Users</h2>
            </div>
            <div id="post_body" class="card-body">
                <form method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" name="search_users" class="form-control" placeholder="Search users" value="<?php echo htmlspecialchars($search_users); ?>">
                        <div class="input-group-append">
                            <button id="create_btn" class="btn" type="submit"><span><i class="bi bi-search mr-2"></i></span>Search</button>
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
                                            <button type="submit" class="btn btn-danger btn-sm mb-2">Delete User</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($_SESSION['role'] == 'admin' && !$user['is_admin']): ?>
                                        <form method="POST" action="">
                                            <input type="hidden" name="item_id" value="<?php echo $user['id']; ?>">
                                            <?php if (!$user['is_moderator']): ?>
                                                <input type="hidden" name="action" value="promote">
                                                <button type="submit" class="btn btn-success btn-sm">Promote to Moderator</button>
                                            <?php else: ?>
                                                <input type="hidden" name="action" value="demote">
                                                <button type="submit" class="btn btn-warning btn-sm">Demote from Moderator</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page_users > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_users=<?php echo $current_page_users - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages_users; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page_users ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_users=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page_users < $total_pages_users): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_users=<?php echo $current_page_users + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="card mb-3">
        <div id="post_header" class="card-header">
            <h2 id="txt8">Posts</h2>
        </div>
        <div id="post_body" class="card-body">
            <form method="GET" action="">
                <div class="input-group mb-3">
                    <input type="text" name="search_posts" class="form-control" placeholder="Search posts" value="<?php echo htmlspecialchars($search_posts); ?>">
                    <div class="input-group-append">
                        <button id="create_btn" class="btn" type="submit"><span><i class="bi bi-search mr-2"></i></span>Search</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Content</th>
                            <th>Game Flair</th>
                            <th>Post Flair</th>
                            <th>Created At</th>
                            <th>Username</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($post = $result_posts->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($post['title']); ?></td>
                                <td><?php echo htmlspecialchars(strip_tags($post['content'])); ?></td>
                                <td><?php echo htmlspecialchars($post['game_flair']); ?></td>
                                <td><?php echo htmlspecialchars($post['post_flair']); ?></td>
                                <td><?php echo htmlspecialchars($post['created_at']); ?></td>
                                <td><?php echo htmlspecialchars($post['username']); ?></td>
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

                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center">
                        <?php if ($current_page_posts > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_posts=<?php echo $current_page_posts - 1; ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages_posts; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page_posts ? 'active' : ''; ?>">
                                <a class="page-link" href="?page_posts=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($current_page_posts < $total_pages_posts): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page_posts=<?php echo $current_page_posts + 1; ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="card">
            <div id="post_header" class="card-header">
                <h2 id="txt8">Manage Flairs</h2>
            </div>
            <div id="post_body" class="card-body">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="flair_type">Flair Type:</label>
                        <select id="flair_type" name="flair_type" class="form-control" required>
                            <option value="game">Game Flair</option>
                            <option value="post">Post Flair</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="flair_name">Flair Name:</label>
                        <input type="text" id="flair_name" name="flair_name" class="form-control" required>
                    </div>
                    <input type="hidden" name="action" value="add_flair">
                    <button type="submit" id="create_btn" class="btn btn-primary">Add Flair</button>
                </form>

                <div class="mt-4">
                    <h3>Existing Game Flairs</h3>
                    <form method="GET" action="">
                        <div class="input-group mb-3">
                            <input type="text" name="search_game_flairs" class="form-control" placeholder="Search game flairs" value="<?php echo htmlspecialchars($search_game_flairs); ?>">
                            <div class="input-group-append">
                                <button id="create_btn" class="btn" type="submit"><span><i class="bi bi-search mr-2"></i></span>Search</button>
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
                                            <input type="hidden" name="item_type" value="game_flair">
                                            <input type="hidden" name="action" value="delete">
                                            <?php
                                            $sql_count = "SELECT COUNT(*) as count FROM game_flairs";
                                            $result_count = $conn->query($sql_count);
                                            $count = $result_count->fetch_assoc()['count'];
                                            if ($count > 1):
                                            ?>
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Game Flair</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page_game_flairs > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_game_flairs=<?php echo $current_page_game_flairs - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages_game_flairs; $i++): ?>
                                <li class="page-item <?php echo $i == $current_page_game_flairs ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_game_flairs=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page_game_flairs < $total_pages_game_flairs): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_game_flairs=<?php echo $current_page_game_flairs + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>

                <div class="mt-4">
                    <h3>Existing Post Flairs</h3>
                    <form method="GET" action="">
                        <div class="input-group mb-3">
                            <input type="text" name="search_post_flairs" class="form-control" placeholder="Search post flairs" value="<?php echo htmlspecialchars($search_post_flairs); ?>">
                            <div class="input-group-append">
                                <button id="create_btn" class="btn" type="submit"><span><i class="bi bi-search mr-2"></i></span>Search</button>
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
                                            <input type="hidden" name="item_type" value="post_flair">
                                            <input type="hidden" name="action" value="delete">
                                            <?php
                                            $sql_count = "SELECT COUNT(*) as count FROM post_flairs";
                                            $result_count = $conn->query($sql_count);
                                            $count = $result_count->fetch_assoc()['count'];
                                            if ($count > 1):
                                            ?>
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Post Flair</button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>

                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php if ($current_page_post_flairs > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_post_flairs=<?php echo $current_page_post_flairs - 1; ?>" aria-label="Previous">
                                        <span aria-hidden="true">&laquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>

                            <?php for ($i = 1; $i <= $total_pages_post_flairs; $i++): ?>
                                <li class="page-item <?php echo $i == $current_page_post_flairs ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page_post_flairs=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($current_page_post_flairs < $total_pages_post_flairs): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page_post_flairs=<?php echo $current_page_post_flairs + 1; ?>" aria-label="Next">
                                        <span aria-hidden="true">&raquo;</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>
</html>