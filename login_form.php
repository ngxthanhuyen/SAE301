<?php
@include 'config.php';

session_start();

$error = [];
$error2 = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['login'])) {
        $email_or_username = mysqli_real_escape_string($conn, $_POST['email_or_username']);
        $password = $_POST['password'];

        //On vérifie si l'utilisateur existe dans la base de données
        $select = "SELECT * FROM users WHERE email = '$email_or_username' OR username='$email_or_username'";
        $result = mysqli_query($conn, $select);

        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_array($result);

            if (password_verify($password, $row['password'])) {
                // Stocker le nom complet dans la session
                $_SESSION['user_name'] = $row['prenom']. ' ' . $row['nom'];
                header('location:user_page.php');
                exit(); 
        } else {
            $error[] = 'Mot de passe invalide!';
        }
    } else {
        $error[] = "Identifiant invalide!";
    }

    if (isset($_POST['register'])) {
        //Cette fonction protège les données contre les injections SQL
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $nom = mysqli_real_escape_string($conn, $_POST['nom']);
        $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $password = $_POST['password'];  
        $cpassword = $_POST['cpassword'];

        //Requête SQL pour chercher l'utilisateur avec l'email fourni dans la base de données
        $select = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $result = mysqli_query($conn, $select);

        //Si le résultat est supérieur à 0, un message d'erreur est stocké dans le tableau $error
        if(mysqli_num_rows($result) > 0) {
            $error2[] = 'Cet utilisateur existe déjà!';
        } else {
            if($password != $cpassword) {
                $error2[] = 'Mot de passe invalide!';
            } else {
                //On hache le mot de passe pour le problème de sécurité
                $hashed_password = password_hash($password, PASSWORD_DEFAULT); 
                $insert = "INSERT INTO users (username, nom, prenom, email, password) VALUES('$username', '$nom', '$prenom', '$email', '$hashed_password')";
                mysqli_query($conn, $insert);
                //On redirige l'utilisateur vers la page de connexion après une inscription réussie
                header('location:login_form.php');
                exit();
            }
        }
    }
}
//Si la connexion réussie
if (isset($_POST['rememberme'])) {
    //On crée un cookie pour se souvenir de l'email pendant 1 an
    setcookie('email_or_username', $email_or_username, time() + (365 * 24 * 3600), "/"); 
} else {
    //On supprime le cookie si l'utilisateur ne veut pas se souvenir de l'email
    if(isset($_COOKIE["email_or_username"])) {
        setcookie('email_or_username', '', time() - 3600, "/"); 
    }
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="static/style/authentification.css"/>
</head>
<body>
    <div class="container" id="container">
        <div class="form-container register-container">
            <form action="register_form.php" method="post">
                <h1>S'inscrire</h1>
                <input type="text" name="username" placeholder="Username">
                <input type="text" name="nom" placeholder="Nom">
                <input type="text" name="prenom" placeholder="Prénom">
                <input type="email" name="email"placeholder="Email">
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
                <?php
                if(!empty($error2)) {
                    foreach($error2 as $error_msg) {
                        echo '<span class="error_msg">' .$error_msg.'</span>';
                    };
                };
                ?>
                <button type="submit" name="register">S'inscrire</button>
                <div class="content">
                    <div class="checkbox">
                        <input type="checkbox" name="checkbox_visiteur" id="checkbox_visiteur">
                        <label for="checkbox_visiteur">ou continuez en tant que visiteur</label>
                    </div>
                </div>     
                <div class="social-container">
                    <a href="#" class="social"><i class="lni lni-facebook-fill"></i></a>
                    <a href="#" class="social"><i class="lni lni-google"></i></a>
                    <a href="#" class="social"><i class="lni lni-linkedin-original"></i></a>
                </div>
            </form>
        </div>
    
        <div class="form-container login-container">
            <form action="login_form.php" method="post">
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
                    <div class="pass-link">
                        <a href="#">Mot de passe oublié ?</a>
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
                <div class="content">
                    <div class="checkbox">
                        <input type="checkbox" name="checkbox_visiteur" id="checkbox_visiteur2">
                        <label for="checkbox_visiteur">ou continuez en tant que visiteur</label>
                    </div>
                </div>
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
    <script src="static/script/script.js"></script>
</body>
</html>