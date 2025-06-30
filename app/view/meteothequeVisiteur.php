<?php
require_once __DIR__ . '/../controller/ControllerMeteothequeVisiteur.php';
require_once __DIR__ . '/../controller/ControllerStations.php';

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Vérifier si une requête AJAX est effectuée
if (isset($_GET['query'])) {
    header('Content-Type: application/json');
    $query = htmlspecialchars($_GET['query']);
    $controller = new ControllerMeteothequeVisiteur();
    try {
        $resultats = $controller->rechercherMeteothequesParStation($query);
        echo json_encode($resultats);
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit; 
}

$controller = new ControllerMeteothequeVisiteur();
$meteotheques = $controller->afficherMeteothequesPubliees();

$controllerStations = new ControllerStations();
$stations = $controllerStations->afficherOptionsStations();

$optionsHTML = "";
if (!empty($stations)) {
    foreach ($stations as $station) {
        $optionsHTML .= "<li class='options' data-value='{$station['num_station']}|{$station['nom']}'>{$station['nom']} - {$station['num_station']}</li>";
    }
} else {
    $optionsHTML = "<li class='options'>Aucune station disponible</li>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/SAE301/static/style/meteothequeVisiteur.css"/>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <title>Météothèque Visiteur</title>
</head>
<body>
    <?php if ($userId) {
            include_once __DIR__ . '/navbar.php';
    } else {
        echo '<nav class="navbar">
            <div class="navbar-logo">
                <a class="logo-link">
                    <img src="/SAE301/static/images/logo.png" alt="Our\'Atmos Logo">
                    <span class="navbar-title">Our\'Atmos</span>
                </a>
            </div>
        </nav>';
    }
?>

    <h1>Météothèques</h1>

    <center>
        <div class="search-container">
            <input type="text" id="search-bar" placeholder="Recherchez une station..." class="search-input">
            <button type="submit" class="search-button" id="searchButton">
                <i class="fas fa-search search-icon"></i>
            </button>
            <div id="suggestions"></div>
        </div>
        <ul id="stationsList" style="display: none;">
            <?php echo $optionsHTML; ?>
        </ul>
    </center>

    <div id="meteotheque-container">
        <?php if (count($meteotheques) > 0): ?>
            <?php foreach ($meteotheques as $meteotheque): ?>
                <div class="meteotheque-block">
                    <span class="user-name top-right"><?= htmlspecialchars($meteotheque['username']); ?></span>
                    <p>Visualisez la météothèque de <?= htmlspecialchars($meteotheque['prenom']) . ' ' . htmlspecialchars($meteotheque['nom']); ?></p>               
                    <a class="ghost" href="?page=detailsMeteotheque&meteotheque_id=<?= htmlspecialchars($meteotheque['meteotheque_id']); ?>&user_id=<?= htmlspecialchars($meteotheque['user_id']); ?>">Découvrir</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Aucune météothèque publiée n'est disponible pour le moment.</p>
        <?php endif; ?>
    </div>

    <?php if (!$userId): ?>
    <div class="favorites-message">
        <p class="info">Vous n'avez pas encore de compte.</p>
        <p class="creer-compte">Créez votre compte maintenant pour accéder à des fonctionnalités exclusives<br> et débloquer tout le potentiel de notre application météorologique !</p>
        <div class="lien">
            <div class="fleche-container">
                <img src="/SAE301/static/images/flecheBas.png" height="90px" width="90px">
            </div>
            <a href="/SAE301/web/frontController.php?page=register_form" class="lien-inscription">Inscrivez-vous
                <i class="lni lni-arrow-right"></i>
            </a>
        </div>
    </div>
<?php endif; ?>


    <script src="/SAE301/static/script/meteothequeVisiteur.js"></script>
</body>
</html>
