<?php
require_once __DIR__ . '/../model/ModelAlerte.php';

class ControllerAlerte {

    private $model;

    public function __construct() {
        $this->model = new ModelAlerte();
    }

    public function afficherAlertes() {
        $alertes = [];
        $message = "";

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user_id = $_SESSION['user_id'] ?? null;
            $station = $_POST['station-hidden'] ?? null;
            $parameter = $_POST['parameter'] ?? null;
            $start_date = $_POST['start_date'] ?? null;
            $end_date = $_POST['end_date'] ?? null;
            $seuil = $_POST['seuil'] ?? null;

            if ($station && $parameter && $start_date && $end_date && $seuil) {
                if (preg_match('/^(?<operator>[<>]=?)?(?<value>-?\d+(\.\d+)?)$/', $seuil, $matches)) {
                    $operator = $matches['operator'] ?? '='; 
                    $seuil_value = (float) $matches['value']; 

                    try {
                        $alertes = $this->model->verifierAlerte($start_date, $end_date, $parameter, $seuil, $station);
                        usort($alertes, function($a, $b) {
                            $dateTimeA = strtotime($a['date'] . ' ' . $a['heure']);
                            $dateTimeB = strtotime($b['date'] . ' ' . $b['heure']);
                            return $dateTimeA - $dateTimeB;
                        });
                    } catch (Exception $e) {
                        $message = $e->getMessage();
                    }
                } else {
                    $message = "Le seuil fourni est invalide.";
                }
            } else {
                $message = "Veuillez remplir tous les champs.";
            }
        }

        return [
            'alertes' => $alertes,
            'message' => $message,
        ];
    }
     // Méthode pour sauvegarder une alerte
     public function sauvegarderAlertes($alertes) {
        $user_id = $_SESSION['user_id'] ?? null; 

        try {
            foreach ($alertes as $alerte) {
                $station = $alerte['station'];
                $parametre = $alerte['parametre'];
                $valeur = $alerte['valeur'];
                $date_alerte = $alerte['date'];
                $heure_alerte = $alerte['heure'];

                $this->model->sauvegarderAlerte($user_id, $station, $parametre, $valeur, $date_alerte, $heure_alerte);
            }
            // Ajouter un message flash de succès
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Les alertes ont été sauvegardées avec succès!'
            ];
        } catch (Exception $e) {
            // Ajouter un message flash d'erreur en cas de problème
            $_SESSION['flash'] = [
                'type' => 'error',
                'message' => 'Erreur lors de la sauvegarde des alertes : ' . $e->getMessage()
            ];
        }
    }    
}
?>