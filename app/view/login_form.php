<?php
include_once __DIR__ . '/../controller/ControllerAuth.php';
$authController = new ControllerAuth();
$error = $authController->login();
$error2 = $authController->register();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="/SAE301/static/style/authentification.css"/>
</head>
<body>
    <nav class="navbar">
        <div class="navbar-logo">
            <a class="logo-link">
                <img src="/SAE301/static/images/logo.png" alt="Our'Atmos Logo">
                <span class="navbar-title">Our'Atmos</span>
            </a>
        </div>
    </nav>
    <div class="container" id="container">
        <div class="form-container register-container">
            <form action="/SAE301/web/frontController.php?page=register_form" method="post" enctype="multipart/form-data">
                <h1>S'inscrire</h1>
                <input type="text" name="username" placeholder="Username">
                <input type="text" name="nom" placeholder="Nom">
                <input type="text" name="prenom" placeholder="Prénom">
                <input type="email" name="email" placeholder="Email">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid" onClick="changer()"/>
                    </span>
                </div>
                <div class="password-container">
                    <input type="password" name="cpassword" placeholder="Confirmez votre mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid" onClick="changer()"/>
                    </span>
                </div>
                <input type="file" name="photo_profil" id="photo_profil" accept="image/*">
                <?php                
                if(!empty($error2)) {
                    foreach($error2 as $error_msg) {
                        echo '<span class="error_msg">' .$error_msg.'</span>';
                    };
                };
                ?>
                <button type="submit" name="register">S'inscrire</button>
                <a href="?page=meteothequeVisiteur&visiteur=true">ou continuez en tant que visiteur</a>    
                <div class="social-container">
                    <a href="#" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="#" class="social"><i class="lni lni-google"></i></a>
                    <a href="#" class="social"><i class="lni lni-linkedin-original"></i></a>
                </div>
            </form>
        </div>
    
        <div class="form-container login-container">
            <form action="/SAE301/web/frontController.php?page=login_form" method="post">
                <h1>Se connecter</h1>
                <input type="text" name="email_or_username" placeholder="Email ou Username">
                <div class="password-container">
                    <input type="password" name="password" placeholder="Mot de passe">
                    <span class="oeil">
                        <img src="https://cdn-icons-png.freepik.com/256/12197/12197891.png?semt=ais_hybrid"onClick="changer()"/>
                    </span>
                </div>
                <div class="content">
                    <div class="checkbox">
                        <input type="checkbox" name="rememberme" id="rememberme" value="<?php if(isset($_COOKIE['email'])) { echo $_COOKIE['email']; } ?>">
                        <label for="rememberme">Se souvenir de moi</label>
                    </div>
                </div>
                <?php
                if(!empty($error)) {
                    foreach($error as $error_msg) {
                        echo '<span class="error_msg">' .$error_msg.'</span>';
                    };
                };
                ?>
                <button type="submit" name="login">Se connecter</button>
                <a href="?page=meteothequeVisiteur&visiteur=true">ou continuez en tant que visiteur</a>    
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
                    <h1 class="title">Ravi de <br> vous retrouver</h1>
                    <p>Si vous avez déjà un compte, connectez-vous pour continuer votre expérience </p>
                    <button class="ghost" id="login">Se connecter
                        <i class="lni lni-arrow-left login"></i>
                    </button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h1 class="title">Commencez votre <br> expérience dès maintenant</h1>
                    <p>Si vous n'avez pas encore de compte, rejoignez-nous et démarrez votre aventure</p>
                    <button class="ghost" id="register">S'inscrire
                        <i class="lni lni-arrow-right register"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="/SAE301/static/script/authentification.js"></script>
</body>
</html>
