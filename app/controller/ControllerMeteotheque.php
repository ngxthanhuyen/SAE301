<?php
require_once __DIR__ . '/../model/ModelMeteotheque.php';

class ControllerMeteotheque {
    private $model;

    public function __construct() {
        $this->model = new ModelMeteotheque();
    }

    public function estFavoris($user_id, $num_station) {
        return $this->model->estFavoris($user_id, $num_station);
    }
    
    public function toggleFavorite($num_station) {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return ['success' => false, 'isFavorite' => false];
        }
        
        $favoris = $this->model->estFavoris($user_id, $num_station);
        if ($favoris) {
            $success = $this->model->supprimerStationMeteotheque($user_id, $num_station);
            $isFavorite = false;

            //On ajoute un message flash pour la suppression
            if ($success) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Cette station a été supprimée de votre météothèque.'
                ];
            }
        } else {
            $success = $this->model->ajouterStationMeteotheque($user_id, $num_station);
            $isFavorite = true;
            if ($success) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Cette station a été ajoutée dans votre météothèque avec succès.'
                ];
            }
        }
        
        return ['success' => $success, 'isFavorite' => $isFavorite];
    }
    
    public function afficherMeteotheque() {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            header('Location: ../view/login_form.php');
            exit;
        }
        return $this->model->getFavoriteStations($user_id);
    }

    public function removeFavorite($num_station) {
        $user_id = $_SESSION['user_id'] ?? null;
        if (!$user_id) {
            return ['success' => false, 'message' => 'Utilisateur non connecté'];
        }
        
        $success = $this->model->supprimerStationMeteotheque($user_id, $num_station);
        if ($success) {
            unset($_SESSION['favoris'][$num_station]);
            $_SESSION['flash'] = [
                'message' => 'Cette station a été supprimée de votre météothèque avec succès!',
                'type' =>  'success'
            ];
        }
        return ['success' => $success, 'message' => $success ? 'Station supprimée des favoris' : 'Erreur lors de la suppression'];
    }
    // Récupère les dernières mesures pour une station donnée
    public function getDernieresMesuresParStation($num_station) {
        return $this->model->getDernieresMesuresParStation($num_station);
    }
    public function getMesuresParStation($num_station, $date = null) {
        // Appelle le modèle pour récupérer les mesures en fonction de la station et de la date
        return $this->model->getMesuresParStation($num_station, $date);
    }     
    public function getMoyenneMesuresParStationDate($num_station_recherche, $date_recherchee) {
        return $this->model->getMoyenneMesuresParStationDate($num_station_recherche, $date_recherchee);
    }
    // Méthode pour obtenir les températures max et min des 7 derniers jours pour une station spécifique
    public function getTemperaturesMaxMinLast7Days($num_station_recherche, $selectedDate) {
        return $this->model->getTemperaturesMaxMinLast7Days($num_station_recherche, $selectedDate);
    }
    public function getWeatherIcon($weatherCode) {
        return $this->model->getWeatherIcon($weatherCode);
    }
    public function compareStations($station1, $station2, $date, $heure) {
        $mesuresStation1 = $this->model->getMesuresParStationDateHeure($station1, $date, $heure);
        $mesuresStation2 = $this->model->getMesuresParStationDateHeure($station2, $date, $heure);
    
        // On vérifie si des mesures sont absentes
        if (empty($mesuresStation1[$heure]) || empty($mesuresStation2[$heure])) {
            return null; 
        }
    
        $parametres = ['temperature', 'humidite', 'precipitation', 'vent', 'pression', 'visibilite'];
        $result = [];
    
        foreach ($parametres as $parametre) {
            $station1Value = $mesuresStation1[$heure][$parametre] ?? 0;
            $station2Value = $mesuresStation2[$heure][$parametre] ?? 0;
    
            $unite = '';
            switch (strtolower($parametre)) {
                case 'temperature':
                    $unite = '°C';
                    break;
                case 'humidite':
                    $unite = '%';
                    break;
                case 'precipitation':
                    $unite = 'mm';
                    break;
                case 'vent':
                    $unite = 'm/s';
                    break;
                case 'pression':
                    $unite = 'hPa';
                    break;
                case 'visibilite':
                    $unite = 'm';
                    break;
            }
    
            // Ajout des valeurs et des unités au résultat
            $result[ucfirst($parametre)] = [
                'station1' => $station1Value,
                'station2' => $station2Value,
                'ecart' => $station2Value - $station1Value,
                'unite' => $unite 
            ];
        }
    
        return $result;
    }
    public function updatePublicationStatus($user_id, $publicationStatus) {
        return $this->model->updatePublicationStatus($user_id, $publicationStatus);
    }
    public function getPublicationStatus($userId) {
        return $this->model->getPublicationStatus($userId);
    }
    // Récupérer les alertes pour un utilisateur
    public function getUserAlerts($userId) {
        return $this->model->getAlertsByUser($userId);
    }
    public function supprimerAlerte() {
        if (isset($_POST['alert_id'])) {
            $alert_id = intval($_POST['alert_id']);
        
            // Appel à la méthode pour supprimer l'alerte dans le modèle
            $resultat = $this->model->supprimerAlerte($alert_id);
        
            // Définir le message flash en fonction du résultat
            if ($resultat) {
                $_SESSION['flash'] = [
                    'type' => 'success',
                    'message' => 'Cet alerte a été supprimée avec succès!'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Erreur lors de la suppression de l\'alerte!'
                ];
            }
        
            // Rediriger pour éviter que le formulaire soit renvoyé si l'utilisateur actualise la page
            header('Location: meteotheque.php');
            exit;
        }
    }    
}