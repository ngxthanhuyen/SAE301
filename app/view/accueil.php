<?php

session_start(); // Démarrer la session
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Données Météorologiques SYNOP</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;400;700&display=swap">
    <link rel="stylesheet" href="../../static/style/accueil.css">
</head>
<body>
    <div id="particles-js"></div> <!-- Conteneur pour l'effet de particules -->

    <!-- Video Background -->
    <div class="video-background">
        <video autoplay muted loop id="background-video">
            <source src="../../static/images/accueil_background.mp4" type="video/mp4">
        </video>
    </div>
    
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
    <main>
        <section id="welcome">
            <div class="welcome-content">
                <h1>Bienvenue à Our'Atmos</h1>
                <p class="typing-effect"></p> 
                <div class="stats">
                <div>
                    <span id="stations-count">0</span> 
                    <p>Stations Météo</p>
                </div>
                <div>
                    <span id="reports-count">0</span> 
                    <p>Relevés</p>
                </div>
            </div>
                <p>Bienvenue sur OUR'ATMOS, votre portail dédié aux observations météorologiques en France métropolitaine et Outre-mer. Plongez dans l'univers fascinant de la météo à travers une interface simple et accessible !</p>
                <p>Faites défiler pour découvrir nos fonctionnalités.</p>
                <div class="welcome-buttons">
                    <a href="StationsAccueil.php" class="btn">Découvrir la carte</a>
                    <a href="alerte.php" class="btn">Déclencher des alertes</a>
                </div>
            </div>
        </section>

        <div class="container">
            <div class="container-content">
                <div class="section map" onclick="window.location.href='../web/frontController.php?page=carte'">
                    <div class="background">
                        <img src="../../static/images/home.png" alt="Carte Météo"> <!-- Image de la carte météo -->
                    </div>
                    <span class="icon">🏠</span>
                    Accueil
                </div>
                <div class="section search" onclick="window.location.href='../web/frontController.php?page=recherche'">
                    <div class="background">
                        <img src="../../static/images/carte_france.png" alt="Recherche Météo"> <!-- Image de recherche météo -->
                    </div>
                    <span class="icon">🗺️</span>
                    Stations
                </div>
                <div class="section chart" onclick="window.location.href='../web/frontController.php?page=all_meteotheques'">
                    <div class="background graph-bg"></div>
                    <div class="background graph-bars">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <span class="icon">🗃️</span>
                    Tableau de bord
                </div>
            </div>

            <div class="container-content">
                <div class="section map" onclick="window.location.href='../web/frontController.php?page=carte'">
                    <div class="background">
                        <img src="../../static/images/undraw_world_bdnk.svg" alt="Carte Météo"> <!-- Image de la carte météo -->
                    </div>
                    <span class="icon">🗺️</span>
                    Cartes climatiques
                </div>
                <div class="section search" onclick="window.location.href='../web/frontController.php?page=recherche'">
                    <div class="background">
                        <img src="../../static/images/alert.png" alt="Recherche Météo"> <!-- Image de recherche météo -->
                    </div>
                    <span class="icon">⚠️</span>
                    Alerte
                </div>
                <div class="section search" onclick="window.location.href='../web/frontController.php?page=recherche'">
                    <div class="background">
                        <img src="../../static/images/star.png" alt="Recherche Météo"> <!-- Image de recherche météo -->
                    </div>
                    <span class="icon">💌</span>
                    Météothèque
                </div>
            </div>
        </div>
    </main>

    <!-- Bouton retour en haut -->
    <div id="back-to-top" onclick="scrollToTop()">↑</div>

     <!-- Inclusion du footer -->
     <?php
        require_once 'footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const menuToggle = document.querySelector(".menu-toggle");
            const navbarMenu = document.querySelector(".navbar-menu");

            menuToggle.addEventListener("click", () => {
                navbarMenu.classList.toggle("active");
            });
        });

        particlesJS("particles-js", {
            "particles": {
                "number": {
                    "value": 80,
                    "density": { "enable": true, "value_area": 800 }
                },
                "color": { "value": "#ffffff" },
                "shape": { "type": "circle" },
                "opacity": { "value": 0.5, "random": false },
                "size": { "value": 3, "random": true },
                "line_linked": { "enable": true, "distance": 150, "color": "#ffffff", "opacity": 0.4, "width": 1 },
                "move": { "enable": true, "speed": 3, "direction": "none", "random": false, "straight": false }
            },
            "interactivity": {
                "events": {
                    "onhover": { "enable": true, "mode": "repulse" }
                }
            }
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

        function animateCounter(elementId, endValue, duration) {
            let startValue = 0;
            let increment = Math.ceil(endValue / duration);
            let counter = setInterval(() => {
                startValue += increment;
                if (startValue >= endValue) {
                    startValue = endValue;
                    clearInterval(counter);
                }
                document.getElementById(elementId).textContent = startValue;
            }, 50);
        }

        window.onload = function () {
            animateCounter("stations-count", 62, 100); // Animer le compteur des stations météo
            animateCounter("reports-count", 29200, 100); // Animer le compteur des relevés
        };

        function setThemeByTime() {
            const hours = new Date().getHours();
            if (hours >= 18 || hours < 6) {
                document.body.classList.add('dark-mode'); // Activer le mode sombre le soir et la nuit
            } else {
                document.body.classList.remove('dark-mode'); // Désactiver le mode sombre le jour
            }
        }
        setThemeByTime();

        function toggleDarkMode() {
            document.body.classList.toggle('dark-mode'); // Basculer le mode sombre
            document.documentElement.classList.toggle('dark-mode');
            const darkModeButton = document.getElementById('darkModeButton');
            if (document.body.classList.contains('dark-mode')) {
                darkModeButton.innerHTML = '☀️ Mode clair'; // Changer le texte du bouton en fonction du mode
            } else {
                darkModeButton.innerHTML = '🌙 Mode sombre';
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const darkModeButton = document.getElementById('darkModeButton');
            if (document.body.classList.contains('dark-mode')) {
                darkModeButton.innerHTML = '☀️ Mode clair'; // Initialiser le texte du bouton en fonction du mode
            } else {
                darkModeButton.innerHTML = '🌙 Mode sombre';
            }
        });

        function toggleNav() {
            const navList = document.getElementById('nav-list');
            navList.classList.toggle('active'); // Basculer l'affichage du menu de navigation
        }

        // Défilement fluide pour les liens internes
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth' // Défilement fluide
                });
            });
        });

        // Ajout d'un effet d'entrée pour les sections au scroll
        const sections = document.querySelectorAll('.section');
        window.addEventListener('scroll', () => {
            sections.forEach(section => {
                const position = section.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                if (position < windowHeight - 100) {
                    section.style.opacity = '1'; // Rendre la section visible
                    section.style.transform = 'translateY(0)'; // Réinitialiser la position
                }
            });
        });

        // Animation du header au scroll
        window.addEventListener('scroll', () => {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.style.background = "rgba(0, 0, 0, 0.9)"; // Changer la couleur de fond du header
            } else {
                header.style.background = "rgba(0, 0, 0, 0.8)";
            }
        });

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' }); // Défilement fluide vers le haut
        }

        window.addEventListener('scroll', () => {
            const backToTop = document.getElementById('back-to-top');   
            if (window.scrollY > 200) {
                backToTop.style.display = 'block'; // Afficher le bouton retour en haut
            } else {
                backToTop.style.display = 'none'; // Masquer le bouton retour en haut
            }
        });

        window.addEventListener('scroll', () => {
            document.querySelectorAll('.background img').forEach(img => {
                const speed = img.getAttribute('data-speed');
                const yPos = -(window.scrollY * speed / 100);
                img.style.transform = `translateY(${yPos}px)`; // Déplacer l'image en fonction du défilement
            });
        });

        document.addEventListener('mousemove', (e) => {
            const icons = document.querySelector('.weather-icons');
            const x = (e.clientX / window.innerWidth) * 100 - 50;
            const y = (e.clientY / window.innerHeight) * 100 - 50;
            icons.style.transform = `translate(${x}px, ${y}px)`; // Déplacer les icônes en fonction de la souris
        });
    </script>
</body>
</html>
