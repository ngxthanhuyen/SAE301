<?php
require_once __DIR__ . '/../controller/ControllerStations.php';
require_once __DIR__ . '/../controller/ControllerDashBoard.php';
ini_set('display_errors', 1);
error_reporting(E_ALL);
$controllerDashboard = new ControllerDashBoard();
$controllerStations = new ControllerStations();

if (isset($_GET['station']) || isset($_GET['region'])) {
    $num_station = $_GET['station'] ?? null;
    $code_region = $_GET['region'] ?? null;

    // Si une date est sélectionnée
    if (isset($_GET['date_selection'])) {
        $date_selection = $_GET['date_selection'];
        if ($num_station) {
            $data = $controllerDashboard->getMesuresEtMoyennesParStationEtDate($num_station, $date_selection);
        } elseif ($code_region) {
            $data = $controllerDashboard->getMesuresEtMoyennesParRegionEtDate($code_region, $date_selection);
        }
        
        // Si des données sont trouvées, renvoyer en JSON
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            // Si aucune donnée n'est trouvée pour la date
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette journée.']);
            exit;
        }
    }
    
    // Si une semaine est sélectionnée
    if (isset($_GET['semaine_selection'])) {
        $date_semaine = $_GET['semaine_selection'];
        if ($num_station) {
            $resultatsSemaines = $controllerDashboard->getMesuresEtMoyennesSemaine($num_station, $date_semaine);
        }

        // Si des mesures et moyennes sont trouvées, renvoyer en JSON
        if ($resultatsSemaines) {
            header('Content-Type: application/json');
            echo json_encode($resultatsSemaines);
            exit;
        } else {
            // Si aucune donnée n'est trouvée pour la semaine
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette semaine.']);
            exit;
        }
    }

    // Si un mois est sélectionné
    if (isset($_GET['mois_selection'])) {
        $date_mois = $_GET['mois_selection'];
        if ($num_station) {
            $data = $controllerDashboard->getMesuresEtMoyennesMois($num_station, $date_mois);
        }

        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour ce mois.']);
            exit;
        }
    }

    // Si une année est sélectionnée
    if (isset($_GET['annee_selection'])) {
        $date_annee = $_GET['annee_selection'];
        if ($num_station) {
            $data = $controllerDashboard->getMesuresEtMoyennesAnnee($num_station, $date_annee);
        } elseif ($code_region) {
            $data = $controllerDashboard->getMesuresEtMoyennesParRegionEtDate($code_region, $date_annee);
        }

        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette année.']);
            exit;
        }
    }
    
    // Si aucune date, semaine, mois ou année n'est spécifiée
    echo json_encode(['error' => 'Veuillez spécifier une date, une semaine, un mois ou une année pour la station.']);
    exit;
}


if (isset($_GET['dept'])) {
    // Extraire uniquement le code du département (avant le "|")
    $code_dept = explode('|', $_GET['dept'])[0];

    // Si une date est sélectionnée
    if (isset($_GET['date_selection'])) {
        $date_selection = $_GET['date_selection'];
        $data = $controllerDashboard->getMesuresEtMoyennesParDeptEtDate($code_dept, $date_selection);
        
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette journée.']);
            exit;
        }
    }

    // Si une semaine est sélectionnée
    if (isset($_GET['semaine_selection'])) {
        $date_semaine = $_GET['semaine_selection'];
        $data = $controllerDashboard->getMesuresEtMoyennesSemaineDept($code_dept, $date_semaine);
        
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette semaine.']);
            exit;
        }
    }

    // Si un mois est sélectionné
    if (isset($_GET['mois_selection'])) {
        $date_mois = $_GET['mois_selection'];
        $data = $controllerDashboard->getMesuresEtMoyennesMoisDept($code_dept, $date_mois);
        
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour ce mois.']);
            exit;
        }
    }

    // Si une année est sélectionnée
    if (isset($_GET['annee_selection'])) {
        $date_annee = $_GET['annee_selection'];
        $data = $controllerDashboard->getMesuresEtMoyennesAnneeDept($code_dept, $date_annee);
        
        if ($data) {
            header('Content-Type: application/json');
            echo json_encode($data);
            exit;
        } else {
            echo json_encode(['error' => 'Aucune donnée trouvée pour cette année.']);
            exit;
        }
    }

    // Si aucune date, semaine, mois ou année n'est spécifiée
    echo json_encode(['error' => 'Veuillez spécifier une date, une semaine, un mois ou une année pour le département.']);
    exit;
}

// Récupération des stations
$stations = $controllerStations->afficherOptionsStations();
$depts = $controllerDashboard->getDepts();
$regs = $controllerDashboard->getReg();

// Génération du HTML pour la liste des stations
$stationsHTML = "";
if (!empty($stations)) {
    foreach ($stations as $station) {
        $stationsHTML .= "<li class='options' data-value='{$station['num_station']}|{$station['nom']}'>{$station['nom']} - {$station['num_station']}</li>";
    }
} else {
    $stationsHTML = "<li class='options'>Aucune station disponible</li>";
}

$deptsHTML = "";
if (!empty($depts)) {
    foreach ($depts as $dept) {
        if (!empty($dept['code_dept']) && !empty($dept['nom_dept'])) {
            $deptsHTML .= "<li class='options' data-value='{$dept['code_dept']}|{$dept['nom_dept']}'>{$dept['nom_dept']} - {$dept['code_dept']}</li>";
        }
    }
} else {
    $deptsHTML = "<li class='options'>Aucun département disponible</li>";
}

$regsHTML = "";
if (!empty($regs)) {
    foreach ($regs as $reg) {
        if (!empty($reg['code_reg']) && !empty($reg['nom_reg'])) {
            $regsHTML .= "<li class='options' data-value='{$reg['code_reg']}|{$reg['nom_reg']}'>{$reg['nom_reg']} - {$reg['code_reg']}</li>";
        }
    }
} else {
    $regsHTML = "<li class='options'>Aucun région disponible</li>";
}

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord</title>
    <link rel="stylesheet" href="/SAE301/static/style/dashboard.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <?php require_once __DIR__ . '/../view/navbar.php'; ?>

    <div id="overlay" style="display: none;"></div>
    <div id="preloader" style="display: none;"></div>

    <h1>Tableau de bord</h1>

    <div class="data-visualization">
        <div class="visualization-content">
            <!-- Granularité Spatiale -->
            <div class="granularity">
                <h2>Granularité Spatiale</h2>
                <div class="btn-group">
                    <div class="station">
                        <label for="station-select">Stations :</label>
                        <div class="select-wrapper">
                            <div class="select-input" id="station-select">Sélectionnez une station</div>
                            <ul class="options-list" id="station-list">
                                <?php echo $stationsHTML; ?>
                            </ul>
                            <input type="hidden" name="station" id="station-hidden">
                        </div>
                    </div>   

                    <div class="station">
                        <label for="dept-select">Départements :</label>
                        <div class="select-wrapper">
                            <div class="select-input" id="dept-select">Sélectionnez un département</div>
                            <ul class="options-list" id="dept-list">
                                <?php echo $deptsHTML; ?>
                            </ul>
                            <input type="hidden" name="dept" id="dept-hidden">
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <div class="station">
                        <label for="region-select">Régions :</label>
                        <div class="select-wrapper">
                            <div class="select-input" id="region-select">Sélectionnez une région</div>
                            <ul class="options-list" id="region-list">
                                <?php echo $regsHTML; ?>
                            </ul>
                            <input type="hidden" name="region" id="region-hidden">
                        </div>  
                    </div>           
                </div>
            </div>

            <!-- Granularité Temporelle -->
            <div class="granularity">
                <h2>Granularité Temporelle</h2>
                <div class="line">
                    <div class="date-picker">
                        <label for="date-journee">Journée :</label>
                        <input type="date" id="date-journee">
                    </div>
                    <div class="date-picker">
                        <label for="date-semaine">Semaine :</label>
                        <input type="week" id="date-semaine">
                    </div>
                </div>

                <div class="line">
                    <div class="date-picker">
                        <label for="mois-select">Mois :</label>
                        <input type="month" id="monthInput" name="month" class="border rounded-md p-2" required>
                    </div>
                    <div class="station">
                        <label for="annee-select">Année :</label>
                        <div class="select-wrapper">
                            <div class="select-input" id="annee-select">Sélectionnez une année</div>
                            <ul class="options-list" id="annee-list">
                                <?php 
                                    for ($i = 2010; $i <= 2025; $i++) {
                                        echo "<li data-value='$i'>$i</li>";
                                    }
                                ?>
                            </ul>
                            <input type="hidden" name="annee" id="annee-hidden">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="validate-btn-container">
            <button type="submit" class="validate-btn" id="valider">Valider</button>
        </div>
    </div>
    <div class="weather-widget" id="weather-widget" style="display: none;">
        <div class="location">
            <i class="fas fa-map-marker-alt"></i>
            <span class="nom-station" id="nom-station"></span>
        </div>
        <div class="weather-info-container">
            <div class="weather-info">
                <div class="date-info">
                    <span class="day" id="day-name"></span>
                    <span class="date" id="full-date"></span>
                </div>
                <div class="temperature">
                    <span class="temp-current" id="temp-current"></span>
                    <div class="max-min">
                        <span class="temp-range">Max: <span id="temp-high">
                        </span> Min: <span id="temp-low"></span></span>
                    </div>
                </div>
            </div>
            <div class="weather-status">
                <span class="weather-icon" id="weather-icon"></span>
                <span class="status-text" id="status-text"></span>
            </div>               
        </div>
    </div>

    <div id="gauge-container" class="gauge-container"></div>
    <div id="table-container" class="table-container"></div>
    <div class="graph-container" id="graph-container"></div>
    <div id="graphContainer" class="graph-row" style="display: none;">
        <div class="graph-temp-container">
            <div id="graph-temp"></div>
        </div>
        <div class="graph-pluvio-container">
            <div id="graph-pluvio"></div>
        </div>
    </div>


    <script src="/SAE301/static/script/dashboard.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
</body>
</html>
