<?php
require_once __DIR__ . '/../controller/ControllerMeteotheque.php';
require_once __DIR__ . '/../controller/ControllerStations.php';
require_once __DIR__ . '/../controller/ControllerUser.php';
require_once __DIR__ . '/../controller/ControllerComparaisons.php';
require_once __DIR__ . '/../controller/ControllerAlerte.php';

$userController = new ControllerUser();
$userData = $userController->index();

$controllerMeteotheque = new ControllerMeteotheque();
$favoriteStations = $controllerMeteotheque->afficherMeteotheque();

$model = new ModelMeteotheque();

// Retirer la station des favoris 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_station']) && !isset($_POST['date'])) {
    $num_station = htmlspecialchars($_POST['num_station']);
    $controllerMeteotheque = new ControllerMeteotheque();

    $result = $controllerMeteotheque->removeFavorite($num_station);

    if ($result['success']) {
        header('Location: ?page=meteotheque&success=1');
        exit;
    } else {
        echo "<p>Erreur : {$result['message']}</p>";
    }
}

// Récupérer les mesures dynamiquement pour un paramètre spécifique
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['parameter']) && isset($_GET['num_station'])) {
    $parameter = htmlspecialchars($_GET['parameter']);
    $num_station = htmlspecialchars($_GET['num_station']);
    $date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : null;

    // Récupérer les mesures pour la station, le paramètre et la date spécifiés
    $mesures = $controllerMeteotheque->getMesuresParStation($num_station, $date);

    // On garde que les données du paramètre demandé
    $data = [];
    foreach ($mesures as $time => $measure) {
        $data[$time] = $measure[$parameter];
    }

    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    error_log('Requête de suppression reçue pour l\'ID : ' . $_POST['id']);

    // Récupérer l'index de la comparaison à supprimer
    $comparisonId = $_POST['id'];

    // Créez une instance du modèle et effectuez la suppression
    $controllerComparaison = new ControllerComparaisons();
    $result = $controllerComparaison->deleteComparison($comparisonId); 

    if ($result['success']) {
        header('Location: ?page=meteotheque&success=1');
        exit;
    } else {
        echo "<p>Erreur : {$result['message']}</p>";
    }
}

$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// Récupérer les alertes de l'utilisateur
$alerts = $controllerMeteotheque->getUserAlerts($userId);

// Vérifier si une suppression d'alerte a été demandée
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alert_id']) && isset($_POST['supprimer_alerte'])) {
    // Appel à la méthode pour supprimer l'alerte
    $controllerMeteotheque->supprimerAlerte($_POST['alert_id']);
}
if (isset($_POST['delete_all_alerts'])) {
    $controllerAlerte = new ControllerAlerte();
    $controllerAlerte->supprimerToutesAlertes();
    // Recharge la page pour voir le changement
    header('Location: ?page=meteotheque');
    exit;
}
// Mettre à jour l'état de publication de la météothèque de l'utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['publication'])) {
    $publicationStatus = $_POST['publication'] ?? 0; 
    $userId = $_SESSION['user_id']; 
    // Récupérer l'état actuel de publication après la mise à jour
    $controllerMeteotheque->updatePublicationStatus($userId, $publicationStatus);

    $response = [
        'success' => true,
        'message' => $publicationStatus ? 'Votre météothèque a été publiée avec succès!' : 'Votre météothèque a été retirée avec succès!',
        'type' => 'success'
    ];

    echo json_encode($response);
    exit;
}
$publication = $controllerMeteotheque->getPublicationStatus($userId);


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Météothèque</title>
    <link rel="stylesheet" href="/SAE301/static/style/meteotheque.css">
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <?php
        require_once 'navbar.php';
    ?>
    <div class="meteotheque-container">
        <h1>Bienvenue à la page Météothèque,
            <span class="username">
                <?php if (isset($userData['prenom']) && isset($userData['nom'])) {
                    echo htmlspecialchars($userData['prenom'] . ' ' . $userData['nom']);
                } ?>
            </span> !
        </h1>
        <p class="subtitle">Visualisez vos stations favorites, explorez et analysez les données météorologiques enregistrées.</p>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash <?php echo $_SESSION['flash']['type']; ?>">
        <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>
        <!-- Si l'utilisateur n'a pas encore de stations en favoris dans sa météothèque -->
        <?php if (empty($favoriteStations)): ?>
            <div class="favorites-message">
                <p class="no-favorites">Vous n'avez pas encore de stations favorites.</p>
                <p class="decouvrir-stations">Découvrez nos stations et ajoutez-en à vos favoris :</p>
                <div class="lien">
                    <div class="fleche-container">
                        <img src="/SAE301/static/images/fleche_bas.png" height="70px" width="70px">
                    </div>
                    <a href="?page=StationsAccueil" class="lien-stations">Découvrir les stations
                        <i class="lni lni-arrow-right"></i>
                    </a>
                </div>
            </div>
        <!-- Si l'utilisateur possède des stations en favoris dans sa météothèque, on les affiche -->
        <?php else: ?>
            <?php foreach ($favoriteStations as $station): ?>
                <div class="station-section">
                    <h2 class="station-name">Station préférée: <span><?= htmlspecialchars($station['nom']) ?></span></h2>
                    <form method="POST" action="?page=meteotheque" class="form">
                        <label for="dateInput-<?= htmlspecialchars($station['num_station']) ?>" class="form-label">Sélectionnez une date</label>
                        <input type="date" name="date" id="datePicker-<?= htmlspecialchars($station['num_station']) ?>" class="form-input" value="<?= isset($_POST['date']) && $_POST['num_station'] == $station['num_station'] ? htmlspecialchars($_POST['date']) : '' ?>">
                        <input type="hidden" name="num_station" value="<?= htmlspecialchars($station['num_station']) ?>">
                        <input type="submit" value="Afficher" class="form-submit">
                    </form>
                    <div class="station-container">
                        <div class="station-card">
                            <div class="infos-container">
                                <p class="infos"><span>Localisation : </span><?= htmlspecialchars($station['libgeo'] ?? '') ?> - <?= htmlspecialchars($station['codegeo'] ?? '') ?>, <?= htmlspecialchars($station['nom_reg'] ?? '') ?> - <?= htmlspecialchars($station['code_reg'] ?? '') ?></p>
                                <p class="infos"><span>
                                <?php 
                                if (!empty($_POST['date']) && $_POST['num_station'] == $station['num_station']) { 
                                    // Si la date est sélectionnée pour cette station
                                    echo "Mesures récupérées à la date :";
                                    $dateSelectionnee = new DateTime($_POST['date']);
                                    echo " " . htmlspecialchars($dateSelectionnee->format("d-m-Y"));
                                } else {
                                    // Dernière mise à jour si aucune date sélectionnée
                                    echo "Dernière mise à jour :";
                                    $controllerStations = new ControllerStations();
                                    $derniereDate = $controllerStations->getDerniereMesureParStation($station['num_station']);
                                    if ($derniereDate) {
                                        $dateUTC = new DateTime($derniereDate);
                                        echo " " . htmlspecialchars($dateUTC->format("d-m-Y à H:i"));
                                    } else {
                                        echo " Aucune mesure trouvée pour cette station.";
                                    }
                                }
                                ?>
                                </span></p>
                            </div>

                            <!-- Récupération des mesures -->
                            <?php
                            if (!empty($_POST['date']) && $_POST['num_station'] == $station['num_station']) {
                                // Récupérer les mesures pour la station et la date sélectionnée
                                $date = $_POST['date'];
                                $num_station = $_POST['num_station'];
                                $moyenneMesures = $controllerMeteotheque->getMoyenneMesuresParStationDate($num_station, $date);
                            } else {
                                // Récupérer les dernières mesures de la station si aucune date spécifique n'est choisie
                                $moyenneMesures = $controllerMeteotheque->getDernieresMesuresParStation($station['num_station']);
                            }
                            ?>

                                <div class="weather-data">
                                    <div class="temperature">
                                    <p><?= htmlspecialchars(round($moyenneMesures['temperature'], 1)) ?>°C</p>
                                </div>
                                <div class="mesures">
                                    <p>Précipitation : <?= htmlspecialchars($moyenneMesures['precipitation']) ?> mm</p>
                                    <p>Humidité : <?= htmlspecialchars($moyenneMesures['humidite']) ?> %</p>
                                    <p>Vent : <?= htmlspecialchars($moyenneMesures['vent']) ?> m/s</p>
                                </div>
                            </div>
                        </div>

                        <form class="remove-favorite-form" method="POST" action="?page=meteotheque">
                            <input type="hidden" name="num_station" value="<?= htmlspecialchars($station['num_station']) ?>">
                            <!-- Bouton cœur -->
                            <button type="button" class="favorite-button" data-num_station="<?= htmlspecialchars($station['num_station']) ?>">
                                <img src="/SAE301/static/images/coeur.png" alt="Icône cœur" class="heart-icon" height="58px" width="60px">
                            </button>
                        </form>

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
                                        $joursSemaine = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
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
    </div>

    <?php
    if (isset($_SESSION['user_id'])) {
        $controllerComparaison = new ControllerComparaisons();
        $comparisons = $controllerComparaison->getUserComparisons($_SESSION['user_id']);
    }
    //Afficher les tableaux de comparaison sauvegardés
    if (!empty($comparisons)): ?>
        <?php foreach ($comparisons as $index => $comparison): ?>
            <div id="user-choices">
                <button type="button" class="close-button" aria-label="Close" data-index="<?= $index ?>">&times;</button>
                <h3 class="centered-title">Tableau de comparaison</h3>
                <div class="choices-container">
                    <div class="choices-row">
                        <div class="station">
                            <span class="label">Station 1 :</span>
                            <span class="value" id="choice-station1"><?= htmlspecialchars($comparison['station1']) ?></span>
                        </div>
                        <div class="station">
                            <span class="label">Station 2 :</span>
                            <span class="value" id="choice-station2"><?= htmlspecialchars($comparison['station2']) ?></span>
                        </div>
                    </div>

                    <div class="choices-row">
                        <div class="date-time">
                            <span class="label">Date sélectionnée :</span>
                            <span class="value" id="choice-date"><?= htmlspecialchars($comparison['date_comp']) ?></span>
                        </div>
                        <div class="date-time">
                            <span class="label">Heure sélectionnée :</span>
                            <span class="value" id="choice-time"><?= htmlspecialchars($comparison['heure_comp']) ?></span>
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
                                <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_s1'])) ?> Pa</td>
                                <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_s2'])) ?> Pa</td>
                                <td><?= htmlspecialchars(preg_replace('/\s+/', '', $comparison['press_ec'])) ?> Pa</td>
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
                <!-- Modal de confirmation pour supprimer le tableau -->
                <div id="confirmTableModal-<?= $index ?>" class="modal hidden">
                    <div class="modal-content">
                        <p>Voulez-vous supprimer ce tableau de comparaison de votre météothèque ?</p>
                        <form id="confirmTableForm-<?= $index ?>" method="POST" action="?page=meteotheque">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($comparison['id']) ?>">
                            <button type="button" class="tableConfirmNo" data-index="<?= $index ?>">Non</button>
                            <button type="submit" class="tableConfirmYes" data-index="<?= $index ?>">Oui</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($alerts)): ?>
    <div class="alerts-section">
        <h2 class='alert-title'>Historique des alertes</h2>
        <form method="post" style="display: flex; justify-content: flex-end; margin-bottom: 10px;">
            <button type="submit" name="delete_all_alerts"
                class="close-button"
                aria-label="Close"
                style="font-size:2rem; background:none; border:none; color:black; cursor:pointer; padding:0 12px; line-height:1;">
                &times;
            </button>
        </form>
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
                <?php foreach ($alerts as $alert): ?>
                    <tr>
                        <td><?= htmlspecialchars($alert['date_alerte']) ?></td>
                        <td><?= htmlspecialchars($alert['heure_alerte']) ?></td>
                        <td><?= htmlspecialchars($alert['station']) ?></td>
                        <td><?= htmlspecialchars($alert['parametre']) ?></td>
                        <td style="position: relative;">
                            <?= htmlspecialchars($alert['valeur'] ?? '') ?>
                            <!-- Formulaire pour supprimer l'alerte -->
                            <form method="POST" action="?page=meteotheque" style="display: inline; position: absolute; top: 0; right: 0;">
                                <input type="hidden" name="alert_id" value="<?= htmlspecialchars($alert['alert_id']) ?>">
                                <button class="delete-button" name="supprimer_alerte" type="submit">×</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($alerts)): ?>
                    <tr class="no-alerts">
                        <td colspan="5">Aucune alerte trouvée pour cette configuration.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>


    <!-- Modal de confirmation pour supprimer la station favorite -->
    <div id="confirmModal" class="modal hidden">
        <div class="modal-content">
            <p>Voulez-vous supprimer cette station de vos favoris ?</p>
            <button id="confirmNo">Non</button>
            <button id="confirmYes">Oui</button>
        </div>
    </div>

    <!-- Afficher le message Flash après la soumission -->
    <div id="messageContainer"></div>
    <?php if (!empty($comparisons) || !empty($favoriteStations)): ?>

    <div class="form-container">
        <form id="publicationForm" method="POST">
            <input type="hidden" id="publicationStatus" name="publication" value="0">
            <div class="publication-option">
                <label for="publication">Publier ma météothèque</label>
                <input type="checkbox" id="publication" name="publication" <?= $publication ? 'checked' : '' ?>>
            </div>
            <div class="center-btn-container">
                <button type="submit" class="btn-publish">Publier<i class="fas fa-save"></i></button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="favorites-message">
        <p class="info">Vous souhaitez visualiser les météothèques des autres utilisateurs ?</p>
        <div class="lien">
            <div class="fleche-container">
                <img src="/SAE301/static/images/flecheBas.png" height="90px" width="90px">
            </div>
            <a href="?page=meteothequeVisiteur" class="lien-inscription">Découvrir toutes les météothèques publiées
                <i class="lni lni-arrow-right"></i>
            </a>
        </div>
    </div>

    <script src="/SAE301/static/script/meteotheque.js"></script>
    <script src="//cdn.jsdelivr.net/npm/chart.js"></script>
</body>
</html>
