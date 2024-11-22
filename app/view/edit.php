<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Édition du Profil</title>
    <link rel="stylesheet" href="../../static/style/edit.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <div class="form-card">
            <h2>Modifier votre profil</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="error"><?= $_SESSION['error'] ?></div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="success"><?= $_SESSION['success'] ?></div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <form action="../controller/ControllerEditProfile.php" method="POST" enctype="multipart/form-data">
                <div class="form-container">
                    <div class="form-content">
                        <label for="nom">Nom :</label>
                        <input type="text" name="nom" id="nom" value="<?= isset($_SESSION['nom']) ? $_SESSION['nom'] : '' ?>"><br><br>
                    </div>

                    <div class="form-content">
                        <label for="prenom">Prénom :</label>
                        <input type="text" name="prenom" id="prenom" value="<?= isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '' ?>"><br><br>
                    </div>
                </div>

                <div class="form-container">
                    <div class="form-content">
                        <label for="username">Pseudo :</label>
                        <input type="text" name="username" id="username" value="<?= isset($_SESSION['username']) ? $_SESSION['username'] : '' ?>"><br><br>
                    </div>
                    <div class="form-content">
                        <label for="email">Email :</label>
                        <input type="text" name="email" id="email" value="<?= isset($_SESSION['email']) ? $_SESSION['email'] : '' ?>"><br><br>
                    </div>
                </div>
               <!-- Gestion de la photo de profil -->
               <div class="input-wrapper">
                    <div class="input-file-container">
                        <input type="file" name="photo_profil" id="photo_profil" onchange="updateFileName()" style="display: none;" />
                        <button type="button" id="custom_file_button" class="btn-file" onclick="document.getElementById('photo_profil').click()">Choisir une photo</button>
                        <span id="file_name">
                            <?= isset($_SESSION['photo_profil']) && $_SESSION['photo_profil'] !== 'avatar.jpg'
                                ? htmlspecialchars(basename($_SESSION['photo_profil'])): 'Aucune photo choisie' ?>
                        </span>
                    </div>
                    <input type="hidden" name="delete_photo" id="delete_photo" value="0">
                    <button type="button" id="delete_button" class="btn-delete" onclick="deletePhoto()">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
                <button type="submit" name="update_profile" class="btn">Mettre à jour le profil</button>
            </form>

            <p><a href="user_page.php">Retour à mon profil</a></p>
        </div>
    </div>
    <script src="../../static/script/edit.js"></script>
</body>
</html>
