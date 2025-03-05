<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../static/style/navbar.css">
    <title></title>
</head>
<body>
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
                <a href="user_page.php">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="#32417a">
                        <path d="M12 12c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm0 2c-3.33 0-10 1.67-10 5v2h20v-2c0-3.33-6.67-5-10-5z"/>
                    </svg>
                </a>
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
    <script>
        document.addEventListener("DOMContentLoaded", () => {
    const menuToggle = document.querySelector(".menu-toggle");
    const navbarMenu = document.querySelector(".navbar-menu");

    menuToggle.addEventListener("click", () => {
        navbarMenu.classList.toggle("active");
    });
});

    </script>
</body>
</html>
