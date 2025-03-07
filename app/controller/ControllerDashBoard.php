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

    public function getMesuresEtMoyennesMois($num_station_recherche, $date_mois) {
        $response = [];

        try {
            $mesuresMois = $this->modelDashBoard->getMesuresMoisStation($num_station_recherche, $date_mois);

            if (empty($mesuresMois)) {
                throw new Exception("Aucune donnée disponible pour ce mois.");
            }

            $moyennesMois = $this->modelDashBoard->calculerMoyenneMois($mesuresMois);

            $response['mesuresMois'] = $mesuresMois;
            $response['moyennesMois'] = $moyennesMois;
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }

        return $response;
    }

    public function getMesuresEtMoyennesAnnee($num_station_recherche, $date_annee) {
        $response = [];

        try {
            $mesuresAnnee = $this->modelDashBoard->getMesuresAnneeStation($num_station_recherche, $date_annee);

            if (empty($mesuresAnnee)) {
                throw new Exception("Aucune donnée disponible pour cette année.");
            }

            $moyennesAnnee = $this->modelDashBoard->calculerMoyenneAnnee($mesuresAnnee);

            $response['mesuresAnnee'] = $mesuresAnnee;
            $response['moyennesAnnee'] = $moyennesAnnee;
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }

        return $response;
    }

    public function getMesuresEtMoyennesParRegionEtDate($code_region, $date_selectionnee) {
        $response = [];
        
        try {
            $mesures = $this->modelDashBoard->getMesuresParRegionEtDate($code_region, $date_selectionnee);
            $moyennes = $this->modelDashBoard->getMoyenneMesuresParRegionDate($code_region, $date_selectionnee);
            
            $date = new DateTime($date_selectionnee);

            $jours = [
                'Monday' => 'Lundi',
                'Tuesday' => 'Mardi',
                'Wednesday' => 'Mercredi',
                'Thursday' => 'Jeudi',
                'Friday' => 'Vendredi',
                'Saturday' => 'Samedi',
                'Sunday' => 'Dimanche'
            ];

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

            $jour_anglais = $date->format('l'); 
            $mois_anglais = $date->format('F'); 
            
            $jour_fr = $jours[$jour_anglais];
            $mois_fr = $mois[$mois_anglais];
            
            $date_formatee = $date->format('d') . ' ' . $mois_fr . ' ' . $date->format('Y');

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
    
    public function getMesuresEtMoyennesSemaineRegion($code_region, $date_semaine) {
        $response = [];
        
        try {
            $mesuresSemaine = $this->modelDashBoard->getMesuresSemaineRegion($code_region, $date_semaine);
            
            if (empty($mesuresSemaine)) {
                throw new Exception("Aucune donnée disponible pour cette semaine.");
            }

            $moyennesSemaine = $this->modelDashBoard->calculerMoyenneSemaineRegion($mesuresSemaine);
            
            $response['mesuresSemaine'] = $mesuresSemaine;
            $response['moyennesSemaine'] = $moyennesSemaine;
            
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }

    public function getMesuresEtMoyennesParDeptEtDate($code_dept, $date_selectionnee) {
        $response = [];
        
        try {
            // Récupérer les stations du département
            $stations = $this->modelDashBoard->getStationsByDept($code_dept);
            
            $mesures = [];
            $moyennes = [];
            $stationsAvecMoyennes = []; // Liste des stations avec leurs moyennes
    
            foreach ($stations as $station) {
                $num_station = $station['num_station'];
                $mesuresStation = $this->modelDashBoard->getMesuresParStationEtDate($num_station, $date_selectionnee);
                $moyennesStation = $this->modelDashBoard->getMoyenneMesuresParStationDate($num_station, $date_selectionnee);
    
                if ($mesuresStation) {
                    $mesures = array_merge($mesures, $mesuresStation);
                }
                if ($moyennesStation) {
                    $moyennes[] = $moyennesStation;
                    // Ajouter la station avec sa moyenne dans la liste
                    $stationsAvecMoyennes[] = [
                        'station' => $this->modelDashBoard->getNomStation($num_station),
                        'moyenne' => $moyennesStation
                    ];
                }
            }
    
            // Calculer les moyennes globales pour le département
            if (!empty($moyennes)) {
                $moyennesGlobales = $this->calculerMoyennesGlobales($moyennes);
                $response['moyennes'] = $moyennesGlobales;
            }
    
            $response['mesures'] = $mesures;
            $response['stations'] = $stationsAvecMoyennes; // Ajouter la liste des stations avec moyennes
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }
    

    private function calculerMoyennesGlobales($moyennes) {
        $totaux = [
            'temperature' => 0,
            'vent' => 0,
            'humidite' => 0,
            'pression' => 0
        ];
    
        $comptes = [
            'temperature' => 0,
            'vent' => 0,
            'humidite' => 0,
            'pression' => 0
        ];
    
        foreach ($moyennes as $moyenne) {
            if (isset($moyenne['temperature'])) {
                $totaux['temperature'] += $moyenne['temperature'];
                $comptes['temperature']++;
            }
            if (isset($moyenne['vent'])) {
                $totaux['vent'] += $moyenne['vent'];
                $comptes['vent']++;
            }
            if (isset($moyenne['humidite'])) {
                $totaux['humidite'] += $moyenne['humidite'];
                $comptes['humidite']++;
            }
            if (isset($moyenne['pression'])) {
                $totaux['pression'] += $moyenne['pression'];
                $comptes['pression']++;
            }
        }
    
        $moyennesGlobales = [];
        foreach ($totaux as $parametre => $total) {
            $moyennesGlobales[$parametre] = ($comptes[$parametre] > 0) ? round($total / $comptes[$parametre], 2) : null;
        }
    
        return $moyennesGlobales;
    }

    public function getMesuresEtMoyennesSemaineDept($code_dept, $date_semaine) {
        $response = [];
        
        try {
            // Récupérer les stations du département
            $stations = $this->modelDashBoard->getStationsByDept($code_dept);
            
            $mesuresSemaine = [];
            $moyennesSemaine = [];
            $stationsAvecMoyennes = []; // Liste des stations avec leurs moyennes
    
            foreach ($stations as $station) {
                $num_station = $station['num_station'];
                $mesuresStation = $this->modelDashBoard->getMesuresSemaineStation($num_station, $date_semaine);
                $moyennesStation = $this->modelDashBoard->calculerMoyenneSemaine($mesuresStation);
    
                if ($mesuresStation) {
                    $mesuresSemaine = array_merge($mesuresSemaine, $mesuresStation);
                }
                if ($moyennesStation) {
                    $moyennesSemaine[] = $moyennesStation;
                    // Ajouter la station avec sa moyenne dans la liste
                    $stationsAvecMoyennes[] = [
                        'station' => $this->modelDashBoard->getNomStation($num_station),
                        'moyenne' => $moyennesStation
                    ];
                }
            }
    
            // Calculer les moyennes globales pour la semaine
            if (!empty($moyennesSemaine)) {
                $moyennesGlobales = $this->calculerMoyennesGlobales($moyennesSemaine);
                $response['moyennesSemaine'] = $moyennesGlobales;
            }
    
            $response['mesuresSemaine'] = $mesuresSemaine;
            $response['stations'] = $stationsAvecMoyennes; // Ajouter la liste des stations avec moyennes
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }
    
    
    public function getMesuresEtMoyennesMoisDept($code_dept, $date_mois) {
        $response = [];
        
        try {
            // Récupérer les stations du département
            $stations = $this->modelDashBoard->getStationsByDept($code_dept);
            
            $mesuresMois = [];
            $moyennesMois = [];
    
            foreach ($stations as $station) {
                $num_station = $station['num_station'];
                $mesuresStation = $this->modelDashBoard->getMesuresMoisStation($num_station, $date_mois);
                $moyennesStation = $this->modelDashBoard->calculerMoyenneMois($mesuresStation);
    
                if ($mesuresStation) {
                    $mesuresMois = array_merge($mesuresMois, $mesuresStation);
                }
                if ($moyennesStation) {
                    $moyennesMois[] = $moyennesStation;
                }
            }
    
            // Calculer les moyennes globales pour le mois
            if (!empty($moyennesMois)) {
                $moyennesGlobales = $this->calculerMoyennesGlobales($moyennesMois);
                $response['moyennesMois'] = $moyennesGlobales;
            }
    
            $response['mesuresMois'] = $mesuresMois;
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }
    
    public function getMesuresEtMoyennesAnneeDept($code_dept, $date_annee) {
        $response = [];
        
        try {
            // Récupérer les stations du département
            $stations = $this->modelDashBoard->getStationsByDept($code_dept);
            
            $mesuresAnnee = [];
            $moyennesAnnee = [];
    
            foreach ($stations as $station) {
                $num_station = $station['num_station'];
                $mesuresStation = $this->modelDashBoard->getMesuresAnneeStation($num_station, $date_annee);
                $moyennesStation = $this->modelDashBoard->calculerMoyenneAnnee($mesuresStation);
    
                if ($mesuresStation) {
                    $mesuresAnnee = array_merge($mesuresAnnee, $mesuresStation);
                }
                if ($moyennesStation) {
                    $moyennesAnnee[] = $moyennesStation;
                }
            }
    
            // Calculer les moyennes globales pour l'année
            if (!empty($moyennesAnnee)) {
                $moyennesGlobales = $this->calculerMoyennesGlobales($moyennesAnnee);
                $response['moyennesAnnee'] = $moyennesGlobales;
            }
    
            $response['mesuresAnnee'] = $mesuresAnnee;
        } catch (Exception $e) {
            $response = ['error' => $e->getMessage()];
        }
        
        return $response;
    }
}
?>
