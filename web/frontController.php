<?php
define('ROOT', dirname(__DIR__));

if (session_status() == PHP_SESSION_NONE) {
    session_start();
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
        require(ROOT . '/app/view/meteotheque.php');
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
}
?>