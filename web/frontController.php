<?php
define('ROOT', dirname(__DIR__));

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Par défaut, définir l'utilisateur comme visiteur si aucune session utilisateur n'existe
if (!isset($_SESSION['user_id'])) {
    $_SESSION['is_visiteur'] = true;
} else {
    unset($_SESSION['is_visiteur']); // Retirer le statut de visiteur si l'utilisateur est connecté
}

// Vérifier si l'utilisateur tente d'accéder à une fonctionnalité restreinte
$restrictedPages = ['user_page', 'edit', 'dashboard', 'meteotheque', 'StationsAccueil', 'StationsInfos', 'climatique', 'alerte']; 
if (isset($_GET['page']) && in_array($_GET['page'], $restrictedPages) && !isset($_SESSION['user_id'])) {
    // Rediriger vers la page de connexion si non connecté
    header("Location: ?page=login_form");
    exit;
}

$page = isset($_GET['page']) ? $_GET['page'] : 'accueil';

switch ($page) {
    case 'accueil':
        require(ROOT . '/app/view/accueil.php');
        break;
    case 'alerte':
        require(ROOT . '/app/view/alerte.php');
        break;
    case 'climatique':
        require(ROOT . '/app/view/climatique.php');
        break;
    case 'dashboard':
        require(ROOT . '/app/view/dashboard.php');
        break;
    case 'admin_page': 
        require(ROOT . '/app/view/admin_page.php');
        break;
    case 'detailsMeteotheque':
        require(ROOT . '/app/view/detailsMeteotheque.php');
        break;
    case 'edit':
        require(ROOT . '/app/view/edit.php');
        break;
    case 'login_form':
        require(ROOT . '/app/view/login_form.php');
        break;
    case 'login':
        require(ROOT . '/app/controller/ControllerAuth.php');
        $authController = new ControllerAuth();
        $authController->login();
        break;
    case 'logout':
        require(ROOT . '/app/view/logout.php');
        break;
    case 'meteotheque':
        if (isset($_GET['parameter']) && isset($_GET['num_station'])) {
            require(ROOT . '/app/controller/ControllerMeteotheque.php');
            $controller = new ControllerMeteotheque();
            $parameter = htmlspecialchars($_GET['parameter']);
            $num_station = htmlspecialchars($_GET['num_station']);
            $date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : null;

            // Récupérer les mesures pour la station et la date spécifiées
            $data = $controller->getMesuresParStation($num_station, $date);

            // Filtrer les données pour inclure uniquement le paramètre demandé
            $filteredData = [];
            foreach ($data as $time => $measure) {
                if (isset($measure[$parameter])) {
                    $filteredData[$time] = $measure[$parameter];
                }
            }

            // Retourner les données filtrées en JSON
            header('Content-Type: application/json');
            echo json_encode($filteredData);
            exit;
        } else {
            require(ROOT . '/app/view/meteotheque.php');
        }
        break;
    case 'meteothequeVisiteur':
        require(ROOT . '/app/view/meteothequeVisiteur.php');
        break;
    case 'register_form':
        require(ROOT . '/app/view/register_form.php');
        break;
    case 'register':
        require(ROOT . '/app/controller/ControllerAuth.php');
        $authController = new ControllerAuth();
        $authController->register();
        break;
    case 'StationsAccueil':
        require(ROOT . '/app/view/StationsAccueil.php');
        break;
    case 'StationsInfos':
        require(ROOT . '/app/view/StationsInfos.php');
        break;
    case 'user_page':
        require(ROOT . '/app/view/user_page.php');
        break;
    default:
        require(ROOT . '/app/view/404.php');
        break;

    case 'edit_user':
        require(ROOT . '/app/view/edit_user.php');
        break;
    case 'deleted_user':
        require(ROOT . '/app/view/deleted_user.php');
        break;
    case 'update_user':
        require(ROOT . '/app/controller/ControllerUser.php');
        $controller = new ControllerUser();
        $controller->updateUser();
        break;
    case 'delete_user':
        require(ROOT . '/app/controller/ControllerUser.php');
        $controller = new ControllerUser();
        $controller->deleteUser();
        break;
}
?>