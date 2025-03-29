<?php

require_once __DIR__ . '/../controller/ControllerStations.php';
require_once __DIR__ . '/../controller/ControllerMeteotheque.php';
include_once __DIR__ . '/../controller/ControllerComparaisons.php';

//Récupération du numéro de station ou du nom de station
$num_station = isset($_GET['num_station']) ? $_GET['num_station'] : '';
$station_name = isset($_GET['station_name']) ? $_GET['station_name'] : '';

//S'il n'y a pas de numéro de station passé en URL
if (empty($num_station) && empty($station_name)) {
    echo "Aucun numéro ou nom de station spécifié.";
    exit();
}

$controller = new ControllerStations();

if ($num_station) {
    $station = $controller->getStationByNum($num_station);
    //Récupération de la dernière mise à ajour de la station en fonction de son numéro ou son nom
    $derniereDate = $controller->getDerniereMesureParStation($num_station);
} elseif ($station_name) {
    $station = $controller->getStationByName($station_name);
    if ($station) {
        $derniereDate = $controller->getDerniereMesureParStation($station['num_station']);
    } else {
        echo "Aucune station trouvée avec ce nom.";
        exit();
    }
}

//On vérifie si l'utilisateur a déjà enregistré cette station dans ses favoris
$controllerMeteotheque = new ControllerMeteotheque();
$userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

if ($userId) {
    $is_favoris = $controllerMeteotheque->estFavoris($userId, $num_station);
} else {
    $is_favoris = false; 
}

//Enregistrement de la station en favoris 
if (isset($_GET['action']) && $_GET['action'] === 'toggleFavorite') {
    $success = $controllerMeteotheque->toggleFavorite($num_station);
    $isFavorite = $controllerMeteotheque->estFavoris($_SESSION['user_id'], $num_station);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //On vérifie quel formulaire a été soumis
    if (isset($_POST['form_type'])) {
        if ($_POST['form_type'] === 'comparer') {
            //Traitement du formulaire de comparaison entre 2 stations
            $station1 = $_POST['station1'] ?? null;
            $station2 = $_POST['station2'] ?? null;
            $date = $_POST['date_selection'] ?? null;
            $heure = $_POST['heure_selection'] ?? null;

            if ($station1 && $station2 && $date && $heure) {
                $result = $controllerMeteotheque->compareStations($station1, $station2, $date, $heure);
                echo json_encode(['success' => true, 'result' => $result]);
                exit;
            } else {
                echo json_encode(['success' => false, 'message' => 'Paramètres manquants']);
                exit;
            }
        //Traitement du formulaire de sauvegarde du tableau de comparaison
        } elseif ($_POST['form_type'] === 'save_comparison') {
            if (!isset($_SESSION['user_id'])) {
                $_SESSION['user_id'] = null;
            }
            //On récupère les données pour créer le tableau de comparaison
            $comparisonData = [
                'user_id' => $_SESSION['user_id'],
                'station1' =>  htmlspecialchars($_POST['station1']),  
                'station2' =>  htmlspecialchars($_POST['station2']), 
                'date_comp' =>  htmlspecialchars($_POST['date']),
                'heure_comp' =>  htmlspecialchars($_POST['time']),
                'temp_s1' => htmlspecialchars($_POST['temp_s1']),
                'temp_s2' => htmlspecialchars($_POST['temp_s2']),
                'temp_ec' => htmlspecialchars($_POST['temp_ec']),
                'hum_s1' => htmlspecialchars($_POST['hum_s1']),
                'hum_s2' => htmlspecialchars($_POST['hum_s2']),
                'hum_ec' => htmlspecialchars($_POST['hum_ec']),
                'prec_s1' => htmlspecialchars($_POST['prec_s1']),
                'prec_s2' => htmlspecialchars($_POST['prec_s2']),
                'prec_ec' => htmlspecialchars($_POST['prec_ec']),
                'vent_s1' => htmlspecialchars($_POST['vent_s1']),
                'vent_s2' => htmlspecialchars($_POST['vent_s2']),
                'vent_ec' => htmlspecialchars($_POST['vent_ec']),
                'press_s1' => htmlspecialchars($_POST['press_s1']),
                'press_s2' => htmlspecialchars($_POST['press_s2']),
                'press_ec' => htmlspecialchars($_POST['press_ec']),
                'vis_s1' => htmlspecialchars($_POST['vis_s1']),
                'vis_s2' => htmlspecialchars($_POST['vis_s2']),
                'vis_ec' => htmlspecialchars($_POST['vis_ec']),
            ];

            //Insérer les données dans la base de données
            $controllerComparaison = new ControllerComparaisons();
            $result = $controllerComparaison->insertComparison($comparisonData);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Station Infos</title>
    <link rel="stylesheet" href="/SAE301/static/style/StationsInfos.css">
    <link rel="stylesheet" href="/SAE301/static/style/navbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/odometer.js/0.4.8/themes/odometer-theme-default.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
        require_once 'navbar.php';
    ?>
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash <?php echo $_SESSION['flash']['type']; ?>">
        <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>
    <!-- Conteneur principal -->
    <div class="station-container">
        <div class="station-header">
            <h1><img src="/SAE301/static/images/marqueur_station.png">Station : <span><?php echo htmlspecialchars($station['nom'] ?? ''); ?></span></h1>
            <h2 class="station-subtitle"><?php echo htmlspecialchars($station['libgeo'] ?? ''); ?> - <?php echo htmlspecialchars($station['codegeo'] ?? ''); ?>, <?php echo htmlspecialchars($station['nom_dept'] ?? ''); ?> - <?php echo htmlspecialchars($station['code_dept'] ?? ''); ?>, <?php echo htmlspecialchars($station['nom_reg'] ?? ''); ?> - <?php echo htmlspecialchars($station['code_reg'] ?? ''); ?></h2>
            <button id="favorite-button" 
                data-num-station="<?php echo htmlspecialchars($station['num_station']); ?>" 
                data-favorite="<?php echo $is_favoris ? 'true' : 'false'; ?>" 
                class="<?php echo $is_favoris ? 'active' : ''; ?>">
                <i class="fas fa-heart"></i>
            </button>
        </div>
        <!-- Fenêtre modale -->
        <div id="confirmation-modal" class="modal-custom hidden">
            <div class="modal-content">
                <p id="modal-message"></p>
                <div class="modal-actions">
                    <button id="modal-no" class="modal-button">Non</button>
                    <button id="modal-yes" class="modal-button">Oui</button>
                </div>
            </div>
        </div>

        <!-- Mise à jour -->
        <div class="last-update-container">
            <div class="last-update">
            <?php 
            if (isset($derniereDate)) {
                $derniereDate = new DateTime($derniereDate);
                echo "Dernière mise à jour: " . htmlspecialchars($derniereDate->format("d-m-Y à H:i"));
            } else {
                echo "Aucune mesure trouvée pour la station $num_station";
            }?>         
            </div>
        </div>
        <!-- Conteneur principal des sections -->
        <div class="content-sections">
            <!-- Paramètres disponibles -->
            <div class="parameters darker">
                <h3>Paramètres disponibles de la station</h3>
                <ul>
                    <li>🌡️ Température<span class="explications">: Mesure de la chaleur ou de la fraîcheur de l'air, en degrés Celsius (°C).</span></li>
                    <li>💧 Humidité <span class="explications">: La quantité de vapeur d'eau présente dans l'air, exprimée en pourcentage.</span></li>
                    <li>⛅ Pression atmosphérique <span class="explications">: Force exercée par l'air sur la surface terrestre, mesurée en Pa.</span></li>
                    <li>🌬️ Vitesse et direction du vent <span class="explications">: Mesure de la vitesse du vent, en mètres par seconde (m/s).</span></li>
                    <li>🌧️ Précipitation <span class="explications">: Quantité de pluie, neige mesurée en mm (millimètres).</span></li>
                    <li>👁️ Visibilité <span class="explications">: Distance maximale à laquelle un objet peut être vu clairement, en mètres(m).</span></li>
                </ul>
            </div>
            <!-- Informations générales -->
            <div class="general-info">
                <h3>Infos générales</h3>
                <ul>
                    <li><span>Numéro de station : </span><?php echo htmlspecialchars($station['num_station'] ?? ''); ?></li>
                    <li><span>Latitude & Longitude : </span><?php echo htmlspecialchars($station['latitude'] ?? ''); ?>, <?php echo htmlspecialchars($station['longitude'] ?? ''); ?></li>
                    <li><span>Altitude: </span><?php echo htmlspecialchars($station['altitude'] ?? ''); ?></li>
                    <li><span>Nom de l’EPCI : </span><?php echo htmlspecialchars($station['nom_epci'] ?? ''); ?> (<?php echo htmlspecialchars($station['code_epci'] ?? ''); ?>)</li>
                    <li><span>Commune : </span><?php echo htmlspecialchars($station['libgeo'] ?? ''); ?> - <?php echo htmlspecialchars($station['codegeo'] ?? ''); ?></li>
                    <li><span>Département : </span><?php echo htmlspecialchars($station['nom_dept'] ?? ''); ?> - <?php echo htmlspecialchars($station['code_dept'] ?? ''); ?></li>
                    <li><span>Région : </span><?php echo htmlspecialchars($station['nom_reg'] ?? ''); ?> - <?php echo htmlspecialchars($station['code_reg'] ?? ''); ?></li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Comparaison entre deux stations-->
    <h2>Comparaison entre stations</h2>
    <form id="compareForm" method="post" action="">
        <input type="hidden" name="form_type" value="comparer">
        <div class="comparaison-container">
            <!-- Section des stations -->
                <?php
                // Appel de la méthode pour obtenir les stations
                $controllerStations = new ControllerStations();
                $stations = $controllerStations->afficherOptionsStations();

                //Vérification si les stations sont récupérées
                if (!empty($stations)) {
                    $optionsHTML = "";
                    foreach ($stations as $station) {
                        $optionsHTML .= "<li class='options' data-value='{$station['num_station']}|{$station['nom']}'>{$station['nom']} - {$station['num_station']}</li>";
                    }
                } else {
                    $optionsHTML = "<li class='options'>Aucune station disponible</li>";
                }
                ?>
            <div class="comparaison-options">
                <div class="station1">
                    <label for="station1-select">Station 1 :</label>
                    <div class="select-wrapper">
                        <div class="select-input" id="station1-select">Sélectionnez une station</div>
                            <ul class="options-list" id="station1-list">
                            <?php echo $optionsHTML; ?>
                            </ul>
                            <input type="hidden" name="station1" id="station1-hidden">
                        </div>
                    </div>

                <div class="station2">
                    <label for="station2-select">Station 2 :</label>
                    <div class="select-wrapper">
                        <div class="select-input" id="station2-select">Sélectionnez une station</div>
                            <ul class="options-list" id="station2-list">
                            <?php echo $optionsHTML; ?>
                            </ul>
                            <input type="hidden" name="station2" id="station2-hidden">
                        </div>
                    </div>
                </div>
            </div>
            <!-- Section date et heure -->
            <div class="comparaison-options">
                <div class="date-picker">
                    <label for="date-selection">Date :</label>
                    <div class="date-container">
                        <input type="date" id="date-selection" name="date_selection" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="heure">
                    <label for="heure-select">Heure :</label>
                    <div class="select-wrapper">
                        <div class="select-input" id="heure-select">Sélectionnez une heure</div>
                            <ul class="options-list" id="heure-list">
                                <li class="options" data-value="00:00">00h</li>
                                <li class="options" data-value="03:00">03h</li>
                                <li class="options" data-value="06:00">06h</li>
                                <li class="options" data-value="09:00">09h</li>
                                <li class="options" data-value="12:00">12h</li>
                                <li class="options" data-value="15:00">15h</li>
                                <li class="options" data-value="18:00">18h</li>
                                <li class="options" data-value="21:00">21h</li>
                            </ul>
                            <input type="hidden" name="heure_selection" id="heure-hidden">
                        </div>
                    </div>
                </div>
                <!-- Bouton de comparaison -->
                <button id="compareBtn" type="submit" name="comparer">Comparer</button>
            </div>
        </div>
    </form>


    <div id="user-choices" class="hidden">
        <h3 class="centered-title">Tableau de comparaison</h3>
        <div class="choices-container">
            <div class="choices-row">
                <div class="station">
                    <span class="label">Station 1 :</span>
                    <span class="value" id="choice-station1"></span>
                </div>
                <div class="station">
                    <span class="label">Station 2 :</span>
                    <span class="value" id="choice-station2"></span>
                </div>
            </div>

            <div class="choices-row">
                <div class="date-time">
                    <span class="label">Date sélectionnée :</span>
                    <span class="value" id="choice-date"></span>
                </div>
                <div class="date-time">
                    <span class="label">Heure sélectionnée :</span>
                    <span class="value" id="choice-time"></span>
                </div>
            </div>
        </div>
        <div class="table-container">
            <!-- Tableau de comparaison -->
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
                        <td><span class="odometer" id="temperature-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> °C</span></td>
                        <td><span class="odometer" id="temperature-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> °C</span></td>
                        <td><span class="odometer" id="temperature-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> °C</span></td>
                    </tr>
                    <tr>
                        <td>Humidité</td>
                        <td><span class="odometer" id="humidite-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> %</span></td>
                        <td><span class="odometer" id="humidite-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> %</span></td>
                        <td><span class="odometer" id="humidite-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> %</span></td>
                    </tr>
                    <tr>
                        <td>Précipitation</td>
                        <td><span class="odometer" id="precipitation-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> mm</span></td>
                        <td><span class="odometer" id="precipitation-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> mm</span></td>
                        <td><span class="odometer" id="precipitation-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> mm</span></td>
                    </tr>
                    <tr>
                        <td>Vent</td>
                        <td><span class="odometer" id="vent-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m/s</span></td>
                        <td><span class="odometer" id="vent-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m/s</span></td>
                        <td><span class="odometer" id="vent-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m/s</span></td>
                    </tr>
                    <tr>
                        <td>Pression</td>
                        <td><span class="odometer" id="pression-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> Pa</span></td>
                        <td><span class="odometer" id="pression-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> Pa</span></td>
                        <td><span class="odometer" id="pression-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> Pa</span></td>
                    </tr>
                    <tr>
                        <td>Visibilité</td>
                        <td><span class="odometer" id="visibilite-station1"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m</span></td>
                        <td><span class="odometer" id="visibilite-station2"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m</span></td>
                        <td><span class="odometer" id="visibilite-ecart"></span><span class="unit" style="font-family: Arial, sans-serif; font-size: 14px;"> m</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <form id="saveComparisonForm" method="POST" action="">
        <input type="hidden" name="save_comparison" value="1">
        <input type="hidden" name="form_type" value="save_comparison">
        <input type="hidden" name="date" id="save-date">
        <input type="hidden" name="time" id="save-time">
        <input type="hidden" name="station1" id="save-station1">
        <input type="hidden" name="station2" id="save-station2">
        <input type="hidden" name="temp_s1" id="save-temp-s1">
        <input type="hidden" name="temp_s2" id="save-temp-s2">
        <input type="hidden" name="temp_ec" id="save-temp-ec">
        <input type="hidden" name="hum_s1" id="save-hum-s1">
        <input type="hidden" name="hum_s2" id="save-hum-s2">
        <input type="hidden" name="hum_ec" id="save-hum-ec">
        <input type="hidden" name="prec_s1" id="save-prec-s1">
        <input type="hidden" name="prec_s2" id="save-prec-s2">
        <input type="hidden" name="prec_ec" id="save-prec-ec">
        <input type="hidden" name="vent_s1" id="save-vent-s1">
        <input type="hidden" name="vent_s2" id="save-vent-s2">
        <input type="hidden" name="vent_ec" id="save-vent-ec">
        <input type="hidden" name="press_s1" id="save-press-s1">
        <input type="hidden" name="press_s2" id="save-press-s2">
        <input type="hidden" name="press_ec" id="save-press-ec">
        <input type="hidden" name="vis_s1" id="save-vis-s1">
        <input type="hidden" name="vis_s2" id="save-vis-s2">
        <input type="hidden" name="vis_ec" id="save-vis-ec">
        <button type="submit" id="submit-save" name="save_comparison" class="save-btn">Sauvegarder
            <i class="fas fa-save"></i> 
        </button>
    </form>
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="flash-message <?php echo $_SESSION['flash_message']['type']; ?>">
        <?php echo $_SESSION['flash_message']['message']; ?>
        </div>
        <?php unset($_SESSION['flash_message']); ?>
    <?php endif; ?>

    <!-- Inclusion du footer -->
    <?php
        require_once __DIR__ . '/../view/footer.php';
    ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/sAE301/static/script/stationsInfos.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/odometer.js/0.4.8/odometer.min.js"></script>
</body>
</html>
