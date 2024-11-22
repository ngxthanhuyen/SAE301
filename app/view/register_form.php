<?php
include '../controller/ControllerAuth.php';
$authController = new ControllerAuth();
$error = $authController->register();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S'inscrire</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="../../static/style/authentification.css"/>
    </head>
<body>
    <!--Barre de navigation-->
    <nav class="navbar">
        <ul class="navbar-links">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="#">Stations</a></li>
            <li><a href="#">Tableau <br>de bord</a></li>
            <div class="navbar-logo">
                <a href="accueil.php" class="logo-link">
                    <img src="../../static/images/logo.png" alt="Our'Atmos Logo">
                    <span class="navbar-title">Our'Atmos</span>
                </a>
            </div>
            <li><a href="#">Cartes<br> climatiques</a></li>
            <li><a href="#">Alerte</a></li>
            <li><a href="#">Météothèque</a></li>
        </ul>
        <div class="navbar-user">
            <a href="user_page.php">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#32417a">
                <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/>
            </svg>
            </a>
        </div>
    </nav>

    <div class="container" id="container">
        <div class="form-container login-container">
            <form action="register_form.php" method="post" enctype="multipart/form-data">
                <h1>S'inscrire</h1>
                <input type="text" name="username" placeholder="Username">
                <input type="text" name="nom" placeholder="Nom">
                <input type="text" name="prenom" placeholder="Prénom">
                <input type="email" name="email"placeholder="Email">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid" onClicl="changer()"/>
                    </span>
                </div>
                <div class="password-container">
                    <input type="password" name="cpassword" placeholder="Confirmez votre mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid" onClicl="changer()"/>
                    </span>
                </div>
                 <!-- Champ pour choisir une photo de profil -->
                <input type="file" name="photo_profil" id="photo_profil" accept="image/*">
                <?php
                if (isset($error_register) && !empty($error_register)) {
                    foreach($error_register as $error_msg) {
                        echo '<span class="error_msg">' .$error_msg.'</span>';
                    };
                };
                ?>
                <button type="submit" name="register">S'inscrire</button>
                <a href="user_page.php?visiteur=true">ou continuez en tant que visiteur</a>
                <div class="social-container">
                    <a href="#" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="#" class="social"><i class="lni lni-google"></i></a>
                    <a href="#" class="social"><i class="lni lni-linkedin-original"></i></a>
                </div>
            </form>
        </div>
        <div class="form-container register-container">
            <form action="login_form.php" method="post">
                <h1>Se connecter</h1>
                <input type="text" name="email_or_username" placeholder="Email ou Username">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid" onClicl="changer()"/>
                    </span>
                </div>
                <div class="content">
                    <div class="checkbox">
                        <input type="checkbox" name="checkbox" id="checkbox">
                        <label>Se souvenir de moi</label>
                    </div>
                </div>
                <?php
                if(!empty($error_login)) {
                    foreach($error_login as $error_msg) {
                        echo '<span class="error_msg">' .$error_msg.'</span>';
                    };
                };
                ?>
                <button type="submit" name="login">Se connecter</button>
                <a href="user_page.php?visiteur=true">ou continuez en tant que visiteur</a>             
                <div class="social-container">
                    <a href="#" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="#" class="social"><i class="lni lni-google"></i></a>
                    <a href="#" class="social"><i class="lni lni-linkedin-original"></i></a>
                </div>
            </form>
        </div>

        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h1 class="title">Commencez votre <br> expérience dès maintenant</h1>
                    <p>Si vous n'avez pas encore de compte, rejoignez-nous et démarrez votre aventure</p>
                    <button class="ghost" id="login">S'inscrire
                        <i class="lni lni-arrow-right register"></i>
                    </button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1 class="title">Ravi de <br> vous retrouver</h1>
                    <p>Si vous avez déjà un compte, connectez-vous pour continuer votre expérience </p>
                    <button class="ghost" id="register">Se connecter
                        <i class="lni lni-arrow-left login"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="../../static/script/authentification.js"></script>
</body>
</html>
