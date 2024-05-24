<?php
include 'includes/db.php';
include 'templates/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if ($password == $user['password']) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            if ($user['is_admin']) {
                $_SESSION['role'] = 'admin';
            } elseif ($user['is_moderator']) {
                $_SESSION['role'] = 'moderator';
            } else {
                $_SESSION['role'] = 'user';
            }
            
            header("Location: index.php");
        } else {
            $loginError = "Invalid password.";
        }
    } else {
        $loginError = "No user found.";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h3 class="text-center">Login</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <?php if(isset($loginError)): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo $loginError; ?>
                            </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'templates/footer.php'; ?>
