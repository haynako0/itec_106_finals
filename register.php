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
    include 'includes/db.php';
    include 'templates/header.php';

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'];
        $password = $_POST['password'];
        
        $sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";
        
        if ($conn->query($sql) === TRUE) {
            header("Location: login.php");
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h3 class="text-center">Register</h3>
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
                            <button type="submit" class="btn btn-primary">Register</button>
                        </form>
                    </div>
                </div>
            </div>
            <div id="reg2" class="col-md-6">
                <h2>Or register with:</h2>
                <a id="fb" class="btn mx-1 btn-lg btn-block text-light" href="coming_soon.php"><span><i class="bi bi-facebook m-2"></i></span>Register with Facebook</a>
                <br></br>
                <a class="btn mx-1 btn-danger btn-lg btn-block text-light" href="coming_soon.php"><span><i class="bi bi-envelope m-2"></i>Register with Gmail</a>
                <br></br>
                <a class="btn mx-1 btn-dark btn-lg btn-block text-light" href="coming_soon.php"><span><i class="bi bi-twitter-x m-2"></i>Register with Twitter</a>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>