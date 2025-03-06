<?php
session_start();

require_once __DIR__ . '/../controller/ControllerAlerte.php';
require_once __DIR__ . '/../controller/ControllerStations.php';


$controllerAlerte = new ControllerAlerte();
$data = $controllerAlerte->afficherAlertes();

$alertes = $data['alertes'];
$message = $data['message'];
// Charger les stations
$controllerStations = new ControllerStations();
$stations = $controllerStations->afficherOptionsStations();

// Vérification si l'utilisateur veut sauvegarder les alertes
if (isset($_POST['submit_save'])) {
    $controllerAlerte->sauvegarderAlertes($alertes);
}

$formSubmitted = isset($_POST['submit']);


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Alerte</title>
    <link rel="stylesheet" href="../../static/style/alerte.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php require_once 'navbar.php'; ?>

    <h1>Bienvenue à la page Alerte !</h1>
    <p class="subtitle">Personnalisez vos alertes pour surveiller les paramètres météo clés sur une période définie, avec un seuil que vous définissez vous-même pour indiquer quand la situation devient alarmante.</p>

    <div class="alert-container">
        <h2>Configurer une alerte</h2>
        <form id="alert-form" action="alerte.php" method="POST">
            <!-- Sélection de la station -->
            <div class="dropdown">
                <label for="dropdown-search">Station :</label>
                <?php
                // Si une station a été sélectionnée, on récupère son nom et numéro
                $stationSelectionnee = '';
                if (isset($_POST['station-hidden']) && !empty($_POST['station-hidden'])) {
                    $stationSelectionnee = $_POST['station-hidden']; // Le numéro de la station
                    // On cherche le nom de la station en utilisant son numéro
                    foreach ($stations as $station) {
                        if ($station['num_station'] == $stationSelectionnee) {
                            $stationSelectionnee = htmlspecialchars($station['nom']) . ' - ' . htmlspecialchars($station['num_station']);
                                break;
                        }
                    }
                }
                ?>
                <input type="text" id="dropdown-search" placeholder="Rechercher une station..." autocomplete="off" value="<?= $stationSelectionnee ?>">
                <div id="dropdown-options" class="hidden">
                <?php if (!empty($stations)): ?>
                    <?php foreach ($stations as $station): ?>
                        <div class="dropdown-item" data-value="<?= htmlspecialchars($station['num_station']) ?>">
                            <?= htmlspecialchars($station['nom']) ?> - <?= htmlspecialchars($station['num_station']) ?>
                        </div>
                    <?php endforeach; ?>
                 <?php else: ?>
                    <div class="dropdown-item">Aucune station disponible</div>
                <?php endif; ?>
                </div>
            </div>
            <input type="hidden" id="station-hidden" name="station-hidden" value="<?= htmlspecialchars($_POST['station-hidden'] ?? '') ?>">

            <!-- Paramètre -->
            <label for="parameter">Paramètre :</label>
            <select name="parameter" id="parameter" required>
                <option value="tc" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'tc') ? 'selected' : '' ?>>Température</option>
                <option value="u" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'u') ? 'selected' : '' ?>>Humidité</option>
                <option value="ff" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'ff') ? 'selected' : '' ?>>Vent</option>
                <option value="pres" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'pres') ? 'selected' : '' ?>>Pression</option>
                <option value="vv" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'vv') ? 'selected' : '' ?>>Visibilité</option>
                <option value="rr1" <?= (isset($_POST['parameter']) && $_POST['parameter'] == 'rr1') ? 'selected' : '' ?>>Précipitation</option>
            </select>

            <!-- Dates -->
            <label for="start_date">Date de début :</label>
            <input type="date" name="start_date" id="start_date" required value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>">

            <label for="end_date">Date de fin :</label>
            <input type="date" name="end_date" id="end_date" required value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>">

            <!-- Seuil -->
            <label for="seuil">Seuil :</label>
            <input type="text" name="seuil" class="seuil" placeholder="Indiquez un seuil au-delà duquel vous considérez que la situation devient alarmante..." required value="<?= htmlspecialchars($_POST['seuil'] ?? '') ?>">

            <button name='submit' type="submit">Configurer l'alerte</button>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash <?php echo $_SESSION['flash']['type']; ?>">
            <?php echo $_SESSION['flash']['message']; ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>


        <div id="historique">
            <h2>Historique des alertes</h2>
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
                    <?php if (!empty($alertes)): ?>
                        <?php
                        $joursAlertes = [];
                        foreach ($alertes as $alerte) {
                            $joursAlertes[$alerte['date']] = true; 
                        }
                        // Nombre de jours uniques où des alertes ont été déclenchées
                        $nbJoursAlertes = count($joursAlertes); 
                        ?>
                        <p class="nbAlertes">Nombre de jours où une alerte a été déclenchée : <?= $nbJoursAlertes ?></p>
                        <?php foreach ($alertes as $alerte): ?>
                            <tr>
                                <td><?= htmlspecialchars($alerte['date']) ?></td>
                                <td><?= htmlspecialchars($alerte['heure']) ?></td>
                                <td><?= htmlspecialchars($alerte['station']) ?></td>
                                <td><?= htmlspecialchars($alerte['parametre']) ?></td>
                                <td><?= htmlspecialchars($alerte['valeur']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php elseif ($formSubmitted): ?>
                        <tr class="no-alerts">
                            <td colspan="5">Aucune alerte trouvée pour cette configuration.</td>
                        </tr>
                    <?php else : ?>
                        <tr class="no-alerts">
                            <td colspan="5">Veuillez configurer une alerte pour voir les résultats.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <button type="submit" id="submit-save" name="submit_save" class="save-btn">Sauvegarder
                <i class="fas fa-save"></i> 
            </button>
        </div>
    </form>
    <script src="../../static/script/alerte.js"></script>
</body>
</html>