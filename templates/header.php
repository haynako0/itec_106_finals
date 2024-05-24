<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Game Forum</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="css/styles.css">
</head>
<body>
<header class="bg-dark text-white py-3 mb-4">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="mb-0" style="font-family: 'Comic Sans MS', cursive, sans-serif; color: #ffdd57;">Game Forum</h1>
            <nav class="navbar navbar-expand-lg navbar-dark">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light mx-1" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-light mx-1" href="create_post.php">Create Post</a>
                        </li>
                        <?php if(isset($_SESSION['username'])): ?>
                            <?php if($_SESSION['role'] == 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-light mx-1" href="admin_panel.php">Admin Panel</a>
                                </li>
                            <?php endif; ?>
                            <?php if($_SESSION['role'] == 'moderator' || $_SESSION['role'] == 'admin'): ?>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-light mx-1" href="manage_flairs.php">Manage Flairs</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link btn btn-outline-light mx-1" href="moderator_panel.php">Moderator Panel</a>
                                </li>
                            <?php endif; ?>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-light mx-1" href="logout.php">Logout</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-light mx-1" href="login.php">Login</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link btn btn-outline-light mx-1" href="register.php">Register</a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
        </div>
    </div>
</header>
<div class="container">
