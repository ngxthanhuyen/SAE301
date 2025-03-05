<?php
include_once __DIR__ . '/../controller/ControllerMeteothequeVisiteur.php';
include_once __DIR__ . '/../controller/ControllerStations.php';
include_once __DIR__ . '/../controller/ControllerMeteotheque.php';

$controller = new ControllerMeteothequeVisiteur();
$controllerStations = new ControllerStations();
$controllerMeteotheque = new ControllerMeteotheque();

//On vérifie si un `meteotheque_id` est passé via l'URL
$meteothequeId = isset($_GET['meteotheque_id']) && is_numeric($_GET['meteotheque_id']) ? intval($_GET['meteotheque_id']) : null;

// On récupère l'id de l'utilisateur
$userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;

if ($userId) {
    // Récupérer les stations favorites de l'utilisateur
    $stationsFavorites = $controller->afficherDetailsMeteotheque($userId);
} else {
    $stationsFavorites = [];
}
$stationsFavorites = $controller->afficherDetailsMeteotheque($userId);
$meteotheque = $meteothequeId ? $controller->getMeteothequeById($meteothequeId) : null;
$comparisons = $controller->getTableauxComparaisonByMeteothequeId($meteothequeId);

// Récupérer les mesures dynamiquement pour un paramètre spécifique
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['parameter']) && isset($_GET['num_station'])) {
    header('Content-Type: application/json');
    
    $parameter = htmlspecialchars($_GET['parameter']);
    $num_station = htmlspecialchars($_GET['num_station']);
    
    // Récupérer les mesures pour la station et le paramètre spécifié
    $mesures = $controllerMeteotheque->getMesuresParStation($num_station);

    // Filtrer les données pour le paramètre demandé
    $data = [];
    foreach ($mesures as $time => $measure) {
        $data[$time] = $measure[$parameter];
    }

    echo json_encode($data);
    exit;
}

// Récupération des alertes sauvegardées par l'utilisateur
$alertesSauvegardees = $controller->getAlertesSauvegardeesParUtilisateur($userId);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../static/style/meteotheque.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <title>Détails de la Météothèque</title>
</head>
<body>
    <div class="meteotheque-container">
        <!-- Affichage des informations de la météothèque -->
        <?php if ($meteotheque): ?>
            <h1>Bienvenue à la météothèque de <?= htmlspecialchars($meteotheque['prenom']) . ' ' . htmlspecialchars($meteotheque['nom']); ?> !</h1>       
        <?php endif; ?>

        <!-- Affichage des stations favorites -->
        <?php if (!empty($stationsFavorites)): ?>
            <?php foreach ($stationsFavorites as $station): ?>
            <div class="station-section" data-num_station="<?= htmlspecialchars($station['num_station']) ?>">
                <h2 class="station-name">Station préférée: <span><?= htmlspecialchars($station['nom']) ?></span></h2>
                <div class="station-container">
                    <div class="station-card">
                        <div class="infos-container">
                            <!-- Informations sur la station -->
                            <p class="infos"><span>Localisation : </span><?= htmlspecialchars($station['libgeo'] ?? '') ?> - <?= htmlspecialchars($station['codegeo'] ?? '') ?>, <?= htmlspecialchars($station['nom_reg'] ?? '') ?> - <?= htmlspecialchars($station['code_reg'] ?? '') ?></p>
                            <p class="infos"><span>Dernière mise à jour :</span>
                                <?php 
                                $derniereDate = $controllerStations->getDerniereMesureParStation($station['num_station']);
                                if ($derniereDate) {
                                    $dateUTC = new DateTime($derniereDate);
                                    echo htmlspecialchars($dateUTC->format("d-m-Y à H:i"));
                                }
                                ?>
                            </p>
                        </div>
                        <!-- Récupération des mesures -->
                        <?php $derniereMesure = $controllerMeteotheque->getDernieresMesuresParStation($station['num_station']);?>
                        <div class="weather-data">
                            <div class="temperature">
                                <p><?= htmlspecialchars(round($derniereMesure['temperature'], 1)) ?>°C</p>
                            </div>
                            <div class="mesures">
                                <p>Précipitations : <?= htmlspecialchars($derniereMesure['precipitation']) ?> mm</p>
                                <p>Humidité : <?= htmlspecialchars($derniereMesure['humidite']) ?> %</p>
                                <p>Vent : <?= htmlspecialchars($derniereMesure['vent']) ?> m/s</p>
                            </div>
                        </div>
                    </div>
                    <!-- Sélection des paramètres -->
                    <div class="parameters-selection">
                        <div class="parameter-container">
                            <button type="button" class="parameter-btn" data-param="temperature">Température</button>
                            <div class="separator"></div>
                            <button type="button" class="parameter-btn" data-param="pression">Pression</button>
                            <div class="separator"></div>
                            <button type="button" class="parameter-btn" data-param="vent">Vent</button>
                            <div class="separator"></div>
                            <button type="button" class="parameter-btn" data-param="humidite">Humidité</button>
                            <div class="separator"></div>
                            <button type="button" class="parameter-btn" data-param="visibilite">Visibilité</button>
                            <div class="separator"></div>
                            <button type="button" class="parameter-btn" data-param="precipitation">Précipitation</button>
                        </div>
                        <!-- Conteneur pour le graphique -->
                        <div class="chart-container" id="chart-container-<?= htmlspecialchars($station['num_station']) ?>">
                            <canvas id="dynamic-chart-<?= htmlspecialchars($station['num_station']) ?>"></canvas>
                        </div>
                        <!-- Affichage des mesures des 7 derniers jours -->
                        <div class="weekly-forecast">
                                <?php 
                               $selectedDate = isset($_POST['date']) ? $_POST['date'] : null;
                               
                               if ($station['num_station']) {
                                   // Si l'utilisateur n'a pas sélectionné de date, on prend la dernière mise à jour
                                   if (!$selectedDate) {
                                       $derniereMesure = $controllerMeteotheque->getDernieresMesuresParStation($station['num_station']);
                                       if ($derniereMesure && isset($derniereMesure['date'])) {
                                           $selectedDate = substr($derniereMesure['date'], 0, 10); 
                                           $weatherData = $controllerMeteotheque->getTemperaturesMaxMinLast7Days($station['num_station'], $selectedDate);
                                       }
                                   } else {
                                    $weatherData = $controllerMeteotheque->getTemperaturesMaxMinLast7Days($station['num_station'], $selectedDate);
                                   }
                                }
                               
                                if (!empty($weatherData)): 
                                    foreach ($weatherData as $date => $data):
                                        $timestamp = strtotime($date);
                                        $joursSemaine = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
                                        $jour = ucfirst($joursSemaine[date('w', $timestamp)]);
                                        $icon = $controllerMeteotheque->getWeatherIcon($data['weather']); 
                                ?>
                                <div class="forecast-day">
                                    <p class="day"><?= htmlspecialchars($jour) . " " . date('d', $timestamp) ?></p>
                                    <p class="weather-icon"><?= htmlspecialchars($icon) ?></p> 
                                    <p class="max-temp"><?= htmlspecialchars($data['max']) ?>°C</p>
                                    <p class="min-temp"><?= htmlspecialchars($data['min']) ?>°C</p>
                                </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p>Aucune donnée disponible pour cette station.</p>
                                <?php endif; ?>
                            </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
        <?php if (!empty($comparisons)): ?>
            <?php foreach ($comparisons as $index => $comparison): ?>
                <div id="user-choices">
                    <h3 class="centered-title">Tableau de comparaison</h3>
                    <div class="choices-container">
                        <div class="choices-row">
                            <div class="station">
                                <span class="label">Station 1 :</span>
                                <span class="value"><?= htmlspecialchars($comparison['station1']) ?></span>
                            </div>
                            <div class="station">
                                <span class="label">Station 2 :</span>
                                <span class="value"><?= htmlspecialchars($comparison['station2']) ?></span>
                            </div>
                        </div>

                        <div class="choices-row">
                            <div class="date-time">
                                <span class="label">Date sélectionnée :</span>
                                <span class="value"><?= htmlspecialchars($comparison['date_comp']) ?></span>
                            </div>
                            <div class="date-time">
                                <span class="label">Heure sélectionnée :</span>
                                <span class="value"><?= htmlspecialchars($comparison['heure_comp']) ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- Tableau de comparaison -->
                    <div class="table-container">
                        <table id="comparison-table">
                            <thead>
                                <tr>
                                    <th>Paramètre</th>
                                    <th>Station 1</th>
                                    <th>Station 2</th>
                                    <th>Écart</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>Température</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['temp_s1'])) ?> °C</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['temp_s2'])) ?> °C</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['temp_ec'])) ?> °C</td>
                                </tr>

                                <tr>
                                    <td>Humidité</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['hum_s1'])) ?> %</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['hum_s2'])) ?> %</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['hum_ec'])) ?> %</td>
                                </tr>

                                <tr>
                                    <td>Précipitations</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['prec_s1'])) ?> mm</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['prec_s2'])) ?> mm</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['prec_ec'])) ?> mm</td>
                                </tr>

                                <tr>
                                    <td>Vent</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vent_s1'])) ?> m/s</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vent_s2'])) ?> m/s</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vent_ec'])) ?> m/s</td>
                                </tr>

                                <tr>
                                    <td>Pression</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_s1'])) ?> hPa</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_s2'])) ?> hPa</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_ec'])) ?> hPa</td>
                                </tr>

                                <tr>
                                    <td>Visibilité</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vis_s1'])) ?> m</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vis_s2'])) ?> m</td>
                                    <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['vis_ec'])) ?> m</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (!empty($alertesSauvegardees)): ?>
            <div class="alerts-section">
                <h2 class='alert-title'>Historique des alertes</h2>
                <table id="historique-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Heure</th>
                            <th>Station</th>
                            <th>Paramètre</th>
                            <th>Valeur</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertesSauvegardees as $alerte): ?>
                            <tr>
                                <td><?= htmlspecialchars($alerte['date_alerte']) ?></td>
                                <td><?= htmlspecialchars($alerte['heure_alerte']) ?></td>
                                <td><?= htmlspecialchars($alerte['station']) ?></td>
                                <td><?= htmlspecialchars($alerte['parametre']) ?></td>
                                <td><?= htmlspecialchars($alerte['valeur']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <script src="../../static/script/detailsMeteotheque.js"></script>
    <script src="//cdn.jsdelivr.net/npm/chart.js"></script>
    
</body>
</html>
