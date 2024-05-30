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
        } elseif ($action == 'edit_flair') {
            $flair_id = $_POST['flair_id'];
            $flair_type = $_POST['flair_type'];
            $flair_name = $_POST['flair_name'];
            $sql = "";
            if ($flair_type == 'game') {
                $sql = "UPDATE game_flairs SET name = ? WHERE id = ?";
            } else {
                $sql = "UPDATE post_flairs SET name = ? WHERE id = ?";
            }
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $flair_name, $flair_id);
            if ($stmt->execute()) {
                header("Location: moderator_panel.php");
            } else {
                echo "Error: " . $sql . "<br>" . $stmt->error;
            }
        }
    }

    function deleteFlair($conn, $flair_id, $table) {
        $flair_type = ($table == 'game_flairs') ? 'game' : 'post';
        $sql_count = "SELECT COUNT(*) as count FROM $table";
        $result_count = $conn->query($sql_count);
        $count = $result_count->fetch_assoc()['count'];

        if ($count > 1) {
            $sql_delete_flair = "DELETE FROM $table WHERE id = ?";
            $stmt_delete_flair = $conn->prepare($sql_delete_flair);
            $stmt_delete_flair->bind_param("i", $flair_id);
            $stmt_delete_flair->execute();
        }
    }

    function deleteUser($conn, $user_id) {
        $sql_delete_user = "DELETE FROM users WHERE id = ?";
        $stmt_delete_user = $conn->prepare($sql_delete_user);
        $stmt_delete_user->bind_param("i", $user_id);
        $stmt_delete_user->execute();
    }

    function canDeleteUser($conn, $user_id) {
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

    $sql_users = "SELECT * FROM users";
    $result_users = $conn->query($sql_users);

    $sql_game_flairs = "SELECT * FROM game_flairs";
    $result_game_flairs = $conn->query($sql_game_flairs);

    $sql_post_flairs = "SELECT * FROM post_flairs";
    $result_post_flairs = $conn->query($sql_post_flairs);
    ?>

    <div class="container mt-5">
        <h2 id="txt7" class="mb-3">Moderator Panel</h2>

        <div class="card mb-3">
            <div id="post_header" class="card-header">
                <h2 id="txt8">Users</h2>
            </div>
            <div id="post_body" class="card-body">
                <form method="GET" action="">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Search by Username" name="user_search" value="<?php echo isset($_GET['user_search']) ? htmlspecialchars($_GET['user_search']) : ''; ?>">
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
            </div>
        </div>

        <div class="card mb-3">
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
                                            <input type="hidden" name="flair_id" value="<?php echo $flair['id']; ?>">
                                            <input type="hidden" name="flair_type" value="game">
                                            <input type="hidden" name="flair_name" value="<?php echo htmlspecialchars($flair['name']); ?>">
                                            <?php
                                            $sql_count = "SELECT COUNT(*) as count FROM game_flairs";
                                            $result_count = $conn->query($sql_count);
                                            $count = $result_count->fetch_assoc()['count'];
                                            if ($count > 1):
                                            ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="item_type" value="game_flair">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Game Flair</button>
                                            <?php endif; ?>
                                            <input type="hidden" name="action" value="edit_flair">
                                            <button type="submit" class="btn btn-primary btn-sm">Edit Game Flair</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    <h3>Existing Post Flairs</h3>
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
                                            <input type="hidden" name="flair_id" value="<?php echo $flair['id']; ?>">
                                            <input type="hidden" name="flair_type" value="post">
                                            <input type="hidden" name="flair_name" value="<?php echo htmlspecialchars($flair['name']); ?>">
                                            <?php
                                            $sql_count = "SELECT COUNT(*) as count FROM post_flairs";
                                            $result_count = $conn->query($sql_count);
                                            $count = $result_count->fetch_assoc()['count'];
                                            if ($count > 1):
                                            ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="item_type" value="post_flair">
                                                <button type="submit" class="btn btn-danger btn-sm">Delete Post Flair</button>
                                            <?php endif; ?>
                                            <input type="hidden" name="action" value="edit_flair">
                                            <button type="submit" class="btn btn-sm">Edit Post Flair</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>