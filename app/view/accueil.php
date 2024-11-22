<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our'Atmos</title>
    <link rel="stylesheet" href="../../static/style/accueil.css">
</head>
<body>
    <!--Barre de navigation-->
    <nav class="navbar">
        <ul class="navbar-links">
            <li><a href="#">Accueil</a></li>
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
            <a href="login_form.php"><img src="../../static/images/icon_user.png" alt="User Icon"></a>
        </div>
    </nav>

    <!-- Video Background -->
      <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../../static/images/accueil_background.mp4" type="video/mp4">
        </video>
    </div>
    <section class="hero">
        <h1>Bienvenue à Our'Atmos</h1>
        <p>Bienvenue sur OUR'ATMOS, votre portail dédié aux observations météorologiques en France métropolitaine et Outre-mer. Explorez nos données et analyses pour comprendre les phénomènes climatiques et suivre les conditions en temps réel. Plongez dans l'univers fascinant de la météo à travers une interface simple et accessible !</p>
    </section>

    <!-- Contenu de la page -->
    <section class="feature">
    <h2 class="section-title">Contenu</h2>

    <div class="flex-list">
        <div class="feature-row">
            <div class="feature-container">
                <img src="../../static/images/home-page.png" width="150" height="150" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Accueil</h4>
                    <p class='card-text'>Cette page d'accueil sert de point d'entrée essentiel pour les utilisateurs et les visiteurs de l'application, en mettant en avant une vue d'ensemble claire et engageante de ce que l'application a à offrir. Elle présente les différentes sections accessibles, notamment la carte interactive des stations, le tableau de bord, les cartes climatiques, la page d’alerte, et la météothèque.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>
            
            <div class="feature-container">
                <img src="../../static/images/stations.png" width="150" height="150" alt="Stations" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Stations</h4>
                    <p class='card-text'>Cette page affiche une carte géolocalisée avec des marqueurs de station et offre des options de filtrage par région, département et commune. En cliquant sur un marqueur, l'utilisateur accède à une présentation détaillée de la station sélectionnée, incluant des informations géographiques et des graphiques de données personnalisables en fonction des préférences de l'utilisateur.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img src="../../static/images/dashboard.png" width="150" height="120" alt="Tableau de bord" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Tableau de bord</h4>
                    <p class='card-text'>Cette page propose des visualisations interactives(courbes, histogrammes) et des statistiques basées sur des paramètres choisis. Les utilisateurs peuvent appliquer des filtres par date, espace et type de graphique pour une analyse approfondie des données météorologiques, permettant de lire les données de manière quantitative, ordinaire, voire nominale.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>
        </div>

        <div class="feature-row">
            <div class="feature-container">
                <img class="img-row2" src="../../static/images/climat.png" width="150" height="150" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Cartes climatiques</h4>
                    <p class="card-text">Cette page permet aux utilisateurs de générer des cartes climatiques basées sur divers critères, comme la température, les précipitations ou l’humidité, pour une période donnée. Les filtres de date et de type de mesure sélectionnés permettent de visualiser les conditions météorologiques d'une manière géographique et temporelle pour mieux comprendre les variations climatiques.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img class="img-row2" src="../../static/images/danger.png" width="150" height="150" alt="Stations" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Alerte</h4>
                    <p class="card-text">Cette page d'alerte informe les utilisateurs des événements météorologiques extrêmes en fournissant des notifications en temps réel. Les utilisateurs peuvent configurer leurs alertes selon leurs préférences et suivre un historique des alertes déclenchées chaque fois qu’une station dépasse un seuil prédéfini. Cela permet de garantir un suivi proactif des conditions à risque.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img class="img-row2" src="../../static/images/star.png" width="150" height="150" alt="Tableau de bord" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Météothèque</h4>
                    <p class="card-text">Cette page météothèque permet aux utilisateurs de gérer leurs données météorologiques avec plusieurs fonctionnalités : gestion des stations favorites, affichage des dernières mesures, sélection des types de données et génération de statistiques sur les 7 derniers jours. Elle inclut également un historique de recherches et une fonctionnalité de comparaison entre deux stations.</p>
                </div>
                <div class="button-container">
                    <a href="alertes" class="button">Découvrir</a>
                </div>
            </div>
        </div>
    </div>
    </section>

</body>
</html>