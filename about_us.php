<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Gacha Garden</title>
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
    ?>

    <div class="container mt-5 mb-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div id="about" class="card">
                    <div class="card-body text-center">
                        <h2 class="card-title display-4 mb-4"><strong>About Us</strong></h2>
                    </div>
                    <div class="card-img-top text-center">
                        <img src="img/logo3-removebg-preview.png" class="img-fluid mt-4" alt="Logo" style="max-width: 90%; height: auto;">
                    </div>
                    <div class="card-body">
                        <h2 class="card-text text-center">
                            Welcome to GachaGarden!
                        </h2>
                        <br>
                        <p class="card-text text-center">
                            Step into a world where imagination blooms and creativity thrives! 🌼🌟
                        </p>
                        <p class="card-text text-center">
                            GachaGarden is not just a forum; it's your enchanted oasis of all things gacha. 
                            Whether you're a seasoned gacha guru or a budding collector, here's where you'll find your 
                            community of like-minded enthusiasts, sharing tips, tricks, and the latest trends in gacha life.
                        </p>
                        <p class="card-text text-center">
                            Join us to explore the lush landscape of gacha games, trade your favorite characters, 
                            and showcase your collection in our vibrant gallery. Don't forget to stop by our tea pavilion 
                            for a chat, and you might just uncover some hidden treasures along the way!
                        </p>
                        <p class="card-text text-center">
                            At GachaGarden, the adventure never ends. 
                            Come sow the seeds of friendship and watch your gacha dreams blossom! 🌸💫
                        </p>
                        <p class="card-text text-center">
                            Let's cultivate creativity and connection in our GachaGarden paradise. 
                            See you inside, where every day is a "sun"-sational day! 🌺🌞
                        </p>
                        <br>
                        <br>
                        <br>
                        <p class="card-text">
                            <strong>Members:</strong>                                                           
                        </p>
                        <p class="card-text">
                            Sofer, Erl Teodemar D.
                        </p>
                        <p class="card-text">
                            Bagalso, Riana Alexis C.
                        </p>
                        <p class="card-text">
                            Agojo, Nigel Oliver R.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>
</html>
