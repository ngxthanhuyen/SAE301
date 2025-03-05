<?php
include_once 'navbar.php';
include_once __DIR__ . '/../controller/ControllerUser.php';
$userController = new ControllerUser();
$userData = $userController->index();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Page</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="../../static/style/user_page.css"/>
</head>
<body>
    <div class="container">
        <div class="content">
            <h3>Bienvenue sur notre site!</h3>
            <h1>
                Bonjour, <span>
                <?php 
                if (isset($_SESSION['prenom']) && isset($_SESSION['nom'])) {
                    echo htmlspecialchars($_SESSION['prenom'] . ' ' . $_SESSION['nom'] . ' 👋');
                } else {
                    echo 'Visiteur 👋'; // Par défaut si aucun utilisateur n'est connecté
                }
                ?>
                </span>
            </h1>
            <p>C'est la page 
                <?php 
                if ($userData) {
                    echo htmlspecialchars($userData['role']);
                } else {
                    echo "visiteur"; // Par défaut
                }
                ?>
            </p>
            
            <?php if (!$userData || isset($_SESSION['is_visiteur'])) : ?>
                <!-- Options pour le visiteur non connecté -->
                <a href="login_form.php" class="btn">Se connecter</a>
                <a href="register_form.php" class="btn">S'inscrire</a>
                <a href="logout.php" class="btn">Se déconnecter</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($userData && (!isset($_SESSION['is_visiteur']) || $_SESSION['is_visiteur'] !== true)) : ?>
        <!-- Carte de profil de l'utilisateur connecté -->
        <div class="profile-container d-flex justify-content-center align-items-center">
            <div class="profile-card shadow w-350 p-3 text-center">
                <img src="../upload/<?php echo htmlspecialchars($_SESSION['photo_profil'] ?? 'avatar.jpg'); ?>" alt="Photo de profil">
                <p><span>Pseudo:</span> <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Non défini'; ?></p>
                <p><span>Email:</span> <?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : 'Non défini'; ?></p>
                <p><span>Date de création:</span> <?php echo isset($_SESSION['creation_date']) ? htmlspecialchars($_SESSION['creation_date']) : 'Non définie'; ?></p>
                <a href="edit.php" class="btn btn-primary">Modifier profil</a>
                <a href="logout.php" class="btn btn-secondary">Se déconnecter</a>
            </div>
        </div>
    <?php endif; ?>

</body>
</html> 