<?php
session_start();
include 'includes/db.php';
include 'templates/header.php';

if (!isset($_SESSION['user_id']) || !$_SESSION['role'] == 'admin') {
    header("Location: login.php");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = $_POST['user_id'];
    $action = $_POST['action'];

    if ($action == 'promote') {
        $sql = "UPDATE users SET is_moderator = 1 WHERE id = ?";
    } elseif ($action == 'demote') {
        $sql = "UPDATE users SET is_moderator = 0 WHERE id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        header("Location: admin_panel.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}

$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<div class="container mt-5">
    <h2>Administrator Panel</h2>

    <div class="card mb-3">
        <div class="card-header bg-dark text-white">
            Users
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $result->fetch_assoc()): ?>
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
                                <?php if (!$user['is_admin']): ?>
                                    <form method="POST" action="">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
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

    <p><a href="logout.php" class="btn btn-primary">Logout</a></p>
</div>

<?php include 'templates/footer.php'; ?>
