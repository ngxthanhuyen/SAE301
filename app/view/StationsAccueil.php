<?php 
require_once __DIR__ . '/../view/navbar.php'; 
include_once __DIR__ . '/../controller/ControllerStations.php';

$controller = new ControllerStations();
$stations = $controller->getStations();
$stationsJson = json_encode($stations); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stations Accueil</title>
    <link rel="stylesheet" href="/SAE301/static/style/StationsAccueil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet"> <!-- AOS CSS -->
</head>
<body>
    <main class="stations-accueil" style="margin-top: 100px;">
        <h1 data-aos="zoom-in">Bienvenue à la page Station !</h1> <!-- Animation ajoutée -->
        <div class="search-bar">
            <form method="GET" action="?page=StationsInfos" id="searchForm">
                <input type="hidden" name="page" value="StationsInfos">
                <input type="text" name="station_name" placeholder="Nom de la station" class="search-input" id="searchInput">
                <button type="submit" class="search-button" id="searchButton">
                    <i class="fas fa-search search-icon"></i>
                </button>
            </form>
        </div>
        <div class="maps-container">
            <div class="map-section">
                <button class="map-button">France Métropolitaine</button>
                <div id="map-metropole" class="map"></div>
            </div>
            <div class="map-section outre-mer">
                <button class="map-button">France d'Outre-Mer</button>
                <div id="map-outre-mer" class="map"></div>
                <select class="ocean-select">
                    <option value="">Choisissez une zone</option>
                    <option value="atlantique">Océan Atlantique</option>
                    <option value="indien">Océan Indien</option>
                </select>
            </div>
        </div>
    </main>

    <!-- Inclusion du footer -->
    <?php
        require_once __DIR__ . '/../view/footer.php';
    ?>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script> <!-- AOS JS -->
    <script>
        AOS.init(); // Initialisation d'AOS
        var stations = <?php echo $stationsJson; ?>;
    </script>
    <script src="/SAE301/static/script/stations.js"></script>
</body>
</html>