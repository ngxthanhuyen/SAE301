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
        <div class="navbar-left">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="StationsAccueil.php">Stations</a></li>
            <li><a href="dashboard.php">Tableau<br>de bord</a></li>
        </div>
        <div class="navbar-logo">
            <a href="accueil.php" class="logo-link">
                <img src="../../static/images/logo.png" alt="Our'Atmos Logo">
                <span class="navbar-title">Our'Atmos</span>
            </a>
        </div>
        <div class="navbar-right">
            <li><a href="climatique.php">Cartes<br>climatiques</a></li>
            <li><a href="alerte.php">Alerte</a></li>
            <li><a href="meteotheque.php">Météothèque</a></li>
            <li>
            <div class="navbar-user">
            <a href="login_form.php"><img src="../../static/images/icon_user.png" alt="User Icon"></a>
        </div>
            </li>
        </div>
        <button class="menu-toggle" aria-label="menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        <!-- Barre verticale contenant navbar-left et navbar-right -->
        <div class="navbar-menu">
            <ul class="left">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="StationsAccueil.php">Stations</a></li>
                <li><a href="dashboard.php">Tableau<br>de bord</a></li>
            </ul>
            <ul class="right">
                <li><a href="climatique.php">Cartes<br>climatiques</a></li>
                <li><a href="alerte.php">Alerte</a></li>
                <li><a href="meteotheque.php">Météothèque</a></li>
                <li>
                    <a href="user_page.php">
                        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#32417a">
                            <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/>
                        </svg>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <!-- Video Background -->
      <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../../static/images/accueil_background.mp4" type="video/mp4">
        </video>
    </div>
    <div id="particles-js"></div>
    <section class="hero">
        <h1>Bienvenue à Our'Atmos</h1>
        <p class="typing-effect"></p> 
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
                    <a href="accueil.php" class="button">Découvrir</a>
                </div>
            </div>
            
            <div class="feature-container">
                <img src="../../static/images/stations.png" width="150" height="150" alt="Stations" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Stations</h4>
                    <p class='card-text'>Cette page affiche une carte géolocalisée avec des marqueurs de station et offre des options de filtrage par région, département et commune. En cliquant sur un marqueur, l'utilisateur accède à une présentation détaillée de la station sélectionnée, incluant des informations géographiques et des graphiques de données personnalisables en fonction des préférences de l'utilisateur.</p>
                </div>
                <div class="button-container">
                    <a href="StationsAccueil.php" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img src="../../static/images/dashboard.png" width="150" height="120" alt="Tableau de bord" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Tableau de bord</h4>
                    <p class='card-text'>Cette page propose des visualisations interactives(courbes, histogrammes) et des statistiques basées sur des paramètres choisis. Les utilisateurs peuvent appliquer des filtres par date, espace et type de graphique pour une analyse approfondie des données météorologiques, permettant de lire les données de manière quantitative, ordinaire, voire nominale.</p>
                </div>
                <div class="button-container">
                    <a href="dashboard.php" class="button">Découvrir</a>
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
                    <a href="climatique.php" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img class="img-row2" src="../../static/images/danger.png" width="150" height="150" alt="Stations" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Alerte</h4>
                    <p class="card-text">Cette page d'alerte informe les utilisateurs des événements météorologiques extrêmes en fournissant des notifications en temps réel. Les utilisateurs peuvent configurer leurs alertes selon leurs préférences et suivre un historique des alertes déclenchées chaque fois qu’une station dépasse un seuil prédéfini. Cela permet de garantir un suivi proactif des conditions à risque.</p>
                </div>
                <div class="button-container">
                    <a href="alerte.php" class="button">Découvrir</a>
                </div>
            </div>

            <div class="feature-container">
                <img class="img-row2" src="../../static/images/star.png" width="150" height="150" alt="Tableau de bord" class="card-icon">
                <div class="feature-content">
                    <h4 class="card-title">Météothèque</h4>
                    <p class="card-text">Cette page météothèque permet aux utilisateurs de gérer leurs données météorologiques avec plusieurs fonctionnalités : gestion des stations favorites, affichage des dernières mesures, sélection des types de données et génération de statistiques sur les 7 derniers jours. Elle inclut également un historique de recherches et une fonctionnalité de comparaison entre deux stations.</p>
                </div>
                <div class="button-container">
                    <a href="meteotheque.php" class="button">Découvrir</a>
                </div>
            </div>
        </div>
    </div>
    </section>

    <!-- Inclusion du footer -->
    <?php
        require_once 'footer.php';
    ?>

<script src="../../static/js/particles.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const menuToggle = document.querySelector(".menu-toggle");
        const navbarMenu = document.querySelector(".navbar-menu");

        menuToggle.addEventListener("click", () => {
            navbarMenu.classList.toggle("active");
        });
        const textArray = ["Explorez l'historique météo...", "Analysez les tendances passées...", "Revivez les données climatiques..."];
        let textIndex = 0;
        let charIndex = 0;
        const typingElement = document.querySelector('.typing-effect');

        function typeText() {
            if (charIndex < textArray[textIndex].length) {
                typingElement.innerHTML += textArray[textIndex].charAt(charIndex);
                charIndex++;
                setTimeout(typeText, 100);
            } else {
                setTimeout(() => {
                    charIndex = 0;
                    typingElement.innerHTML = "";
                    textIndex = (textIndex + 1) % textArray.length;
                    typeText();
                }, 2000);
            }
        }

        typeText();
    });
    particlesJS("particles-js", {
        "particles": {
            "number": {
                "value": 100, // Nombre de particules
                "density": {
                    "enable": true,
                    "value_area": 800
                }
            },
            "color": {
                "value": "#ffffff" // Couleur des particules
            },
            "shape": {
                "type": "circle",
                "stroke": {
                    "width": 0,
                    "color": "#000000"
                },
                "polygon": {
                    "nb_sides": 5
                }
            },
            "opacity": {
                "value": 0.5,
                "random": false,
                "anim": {
                    "enable": false,
                    "speed": 1,
                    "opacity_min": 0.1,
                    "sync": false
                }
            },
            "size": {
                "value": 3,
                "random": true,
                "anim": {
                    "enable": false,
                    "speed": 40,
                    "size_min": 0.1,
                    "sync": false
                }
            },
            "line_linked": {
                "enable": true,
                "distance": 150,
                "color": "#ffffff",
                "opacity": 0.4,
                "width": 1
            },
            "move": {
                "enable": true,
                "speed": 6,
                "direction": "none",
                "random": false,
                "straight": false,
                "out_mode": "out",
                "bounce": false,
                "attract": {
                    "enable": false,
                    "rotateX": 600,
                    "rotateY": 1200
                }
            }
        },
        "interactivity": {
            "detect_on": "canvas",
            "events": {
                "onhover": {
                    "enable": true,
                    "mode": "repulse"
                },
                "onclick": {
                    "enable": true,
                    "mode": "push"
                },
                "resize": true
            },
            "modes": {
                "grab": {
                    "distance": 400,
                    "line_linked": {
                        "opacity": 1
                    }
                },
                "bubble": {
                    "distance": 400,
                    "size": 40,
                    "duration": 2,
                    "opacity": 8,
                    "speed": 3
                },
                "repulse": {
                    "distance": 100,
                    "duration": 0.4
                },
                "push": {
                    "particles_nb": 4
                },
                "remove": {
                    "particles_nb": 2
                }
            }
        },
        "retina_detect": true
    });
    </script>
</body>
</html>