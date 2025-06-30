<?php
require_once __DIR__ . '/../controller/ControllerClimatique.php';

// Si le formulaire est soumis en POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dateDebut = $_POST['date_start'];
    $dateFin = $_POST['date_end'];
    $parametre = $_POST['data_type'];

    $controller = new ControllerClimatique();

    // Appeler la méthode pour obtenir les variations en GeoJSON
    $geoJsonData = $controller->getVariationsEnGeoJSON($dateDebut, $dateFin, $parametre);

    //On retourne les données GeoJSON au JavaScript
    echo $geoJsonData;
    exit;  
}
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
    <title>Cartes Climatiques</title>
    <link rel="stylesheet" href="/SAE301/static/style/climatique.css"/>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body>
    <?php
        require_once 'navbar.php';
    ?>

    <div id="overlay" style="display: none;"></div>
    <div id="preloader" style="display: none;"></div>

    <div class="main-container">
        <div class="sidebar">
            <h1>Cartes climatiques</h1>
            <div class="sidebar-container">
                <form action="?page=climatique" method="POST">
                    <div class="form-group">
                        <label for="date-start">Date de début</label>
                        <input type="date" id="date-start" name="date_start" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="date-end">Date de fin</label>
                        <input type="date" id="date-end" name="date_end" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group">
                        <label for="data-type">Type de données</label>
                        <div class="select-wrapper">
                            <div class="select-input" id="data-type-display">Sélectionnez un type</div>
                                <ul class="options-list" id="data-type-list">
                                    <li class="options" data-value="tc">Température</li>
                                    <li class="options" data-value="pres">Pression</li>
                                    <li class="options" data-value="ff">Vent</li>
                                    <li class="options" data-value="u">Humidité</li>
                                    <li class="options" data-value="vv">Visibilité</li>
                                    <li class="options" data-value="rr1">Précipitation</li>
                                </ul>
                            <input type="hidden" name="data_type" id="data-type-hidden">
                            </div>
                        </div>
                    <button type="submit" class="btn-search">Afficher</button>
                </form>
            </div>
        <!-- Légende -->
        <div id="color-legend-container" style="display: none;">
            <div id="color-legend" class="legend"></div>
        </div>
    </div>

        <div class="map-container">
            <div class="zonage-selector">
                <select id="zonage-level">
                    <option value="region">Région</option>
                </select>
            </div>
            <div id="map"></div>
        </div>
    </div>

    <script>
        var stations = <?php echo $stationsJson; ?>;
    </script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="/SAE301/static/script/climatique.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@turf/turf@6.5.0"></script>
</body>
</html>
