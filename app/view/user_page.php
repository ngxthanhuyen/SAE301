<?php
session_start();
@include '../config.php';
include_once __DIR__ . '/../controller/ControllerUser.php';

$userController = new ControllerUser();
$userData = $userController->index();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="../../static/style/userpage.css"/>
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

    <div class="container">
        <div class="content">
            <h3>Bienvenue sur notre site!</h3>
            <h1>Bonjour, <span>
            <?php 
            if (isset($_SESSION['user_id']) && isset($_SESSION['prenom']) && isset($_SESSION['nom'])) {
                echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' 👋');
                } else {
                    echo 'Visiteur' . ' 👋'; //Par défaut si aucun utilisateur n'est connecté
                }
            ?></span>
            </h1>
            <p>C'est la page 
            <?php 
                if ($userData) {
                    echo htmlspecialchars($userData['role']);
                } else {
                    echo "visiteur"; //Par défaut
                }
            ?></p>
             <?php if (!$userData): ?>
            <!-- Options pour le visiteur non connecté -->
            <a href="login_form.php" class="btn">Se connecter</a>
            <a href="register_form.php" class="btn">S'inscrire</a>
            <a href="logout.php" class="btn">Se déconnecter</a>
        <?php endif; ?>
        </div>
    </div>
    <?php if ($userData): ?>
    <!-- Carte de profil de l'utilisateur connecté -->
    <div class="profile-container d-flex justify-content-center align-items-center">
    	<div class="profile-card shadow w-350 p-3 text-center">
            <img src="../upload/<?php echo basename($_SESSION['photo_profil']); ?>">
            <p><span>Pseudo:</span> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Non défini'; ?></p>
            <p><span>Email:</span> <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Non défini'; ?></p>
            <p><span>Date de création:</span> <?php echo isset($_SESSION['creation_date']) ? htmlspecialchars($_SESSION['creation_date']) : 'Non définie'; ?></p>
            <a href="edit.php" class="btn btn-primary">
            	Modifier profile
            </a>
            <a href="logout.php" class="btn btn-secondary">
                Se déconnecter
            </a>
		</div>
        <?php endif; ?>
    </div>
    
    <script src="static/script.js"></script>
</body>
</html>
