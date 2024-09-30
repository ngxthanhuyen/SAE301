<?php
@include 'config.php';

session_start();
if (isset($_SESSION['user_name'])) {
} else {
    echo "Veuillez vous connecter.";
    header('location:login_form.php');
    exit; 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User page</title>
    <link rel="stylesheet" href="static/style/style.css"/>
</head>
<body>
    <div class="container">
        <div class="content">
            <h3>Bonjour, <span> utilisateur</span></h3>
            <h1>Bienvenue <span><?php echo $_SESSION['user_name'] ?></span></h1>
            <p>C'est la page utilisateur</p>
            <a href="login_form.php"class="btn">Se connecter</a>
            <a href="register_form.php" class="btn">S'inscrire</a>
            <a href="logout.php" class="btn">Se déconnecter</a>
        </div>
    </div>
    
    <script src="script.js"></script>
</body>
</html>