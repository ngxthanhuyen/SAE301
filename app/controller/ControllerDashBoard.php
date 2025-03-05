<?php
require_once __DIR__ . '/../model/ModelDashBoard.php';

class ControllerDashBoard {

    private $modelDashBoard;

    public function __construct() {
        $this->modelDashBoard = new ModelDashBoard();
    }

    public function getDepts() {
        return $this->modelDashBoard->getDepts();
    }

    public function getReg() {
        return $this->modelDashBoard->getReg();
    }
    public function getMesuresEtMoyennesParStationEtDate($num_station_recherche, $date_selectionnee) {
        $response = [];
        
        try {
            // Récupérer le nom de la station
            $nom_station = $this->modelDashBoard->getNomStation($num_station_recherche);
    
            // Récupérer les mesures avec icône et description météo
            $mesures = $this->modelDashBoard->getMesuresParStationEtDate($num_station_recherche, $date_selectionnee);
            
            // Récupérer les moyennes
            $moyennes = $this->modelDashBoard->getMoyenneMesuresParStationDate($num_station_recherche, $date_selectionnee);
            
            // Conversion de la date en objet DateTime
            $date = new DateTime($date_selectionnee);
    
            // Tableau pour les jours en français
            $jours = [
                'Monday' => 'Lundi',
                'Tuesday' => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday' => 'Jeudi',
                'Friday' => 'Vendredi',
                'Saturday' => 'Samedi',
                'Sunday' => 'Dimanche'
            ];

            // Tableau pour les mois en français
            $mois = [
                'January' => 'Janvier',
                'February' => 'Février',
                'March' => 'Mars',
                'April' => 'Avril',
                'May' => 'Mai',
                'June' => 'Juin',
                'July' => 'Juillet',
                'August' => 'Août',
                'September' => 'Septembre',
                'October' => 'Octobre',
                'November' => 'Novembre',
                'December' => 'Décembre'
            ];
    
            // Utiliser format pour obtenir le jour de la semaine et le mois en anglais
            $jour_anglais = $date->format('l'); 
            $mois_anglais = $date->format('F'); 
            
            // Remplacer par les noms français
            $jour_fr = $jours[$jour_anglais];
            $mois_fr = $mois[$mois_anglais];
            
            // Formater la date en français (jour, mois et année)
            $date_formatee = $date->format('d') . ' ' . $mois_fr . ' ' . $date->format('Y');
    
            // Ajouter les mesures, moyennes, date, jour et nom de la station à la réponse
            $response['nom_station'] = $nom_station;
            $response['mesures'] = $mesures;
            $response['moyennes'] = $moyennes;
            $response['date_selectionnee'] = $date_formatee;
            $response['jour'] = $jour_fr;
    
            $weatherData = $mesures[0]['weatherIcon']; 
            $weatherInfo = $this->modelDashBoard->getWeatherIcon($weatherData);
            $response['weatherIcon'] = $weatherInfo['icon']; 
            $response['weatherDescription'] = $weatherInfo['description']; 
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }
    public function getMesuresEtMoyennesSemaine($num_station_recherche, $date_semaine) {
        $response = [];
        
        try {
            // Appel de la méthode du modèle pour récupérer les mesures de la semaine
            $mesuresSemaine = $this->modelDashBoard->getMesuresSemaineStation($num_station_recherche, $date_semaine);
            
            // Vérifier si des mesures ont été récupérées pour la semaine
            if (empty($mesuresSemaine)) {
                throw new Exception("Aucune donnée disponible pour cette semaine.");
            }
    
            // Calculer les moyennes à l'aide de la fonction calculerMoyenneSemaine dans le modèle
            $moyennesSemaine = $this->modelDashBoard->calculerMoyenneSemaine($mesuresSemaine);
            
            // Ajouter les mesures et moyennes dans la réponse
            $response['mesuresSemaine'] = $mesuresSemaine;
            $response['moyennesSemaine'] = $moyennesSemaine;
            
        } catch (Exception $e) {
            // Si une erreur survient, retourner un message d'erreur
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }    
}
?>
