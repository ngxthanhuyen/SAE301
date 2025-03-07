<?php
require_once __DIR__ . '/../model/Model.php';

class ModelDashBoard {

    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }
    // Récupérer toutes les départements
    public function getDepts() {
        $pdo = Model::getInstance()->getPdo();
        $sql = "SELECT DISTINCT nom_dept, code_dept FROM stations
                ORDER BY nom_dept ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Récupérer toutes les régions
    public function getReg() {
        $pdo = Model::getInstance()->getPdo();
        $sql = "SELECT DISTINCT nom_reg, code_reg FROM stations
                ORDER BY nom_reg ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getNomStation($num_station) {
        $query = "SELECT nom FROM stations WHERE num_station = :num_station";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':num_station', $num_station, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['nom'] ?? 'Station inconnue';
    }

    public function getStationsByDept($code_dept) {
        $query = "SELECT num_station FROM stations WHERE code_dept = :code_dept";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':code_dept', $code_dept, PDO::PARAM_STR);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    

    public function getMesuresParStationEtDate($num_station_recherche, $date_selectionnee) {
        $mesures = [];
        
        // Définir le type de réponse comme JSON
        header('Content-Type: application/json');
        
        try {
            $num_station_recherche = explode('|', $num_station_recherche)[0];
            $dateSelectionnee = new DateTime($date_selectionnee);
            $startDate = $dateSelectionnee->format('Y-m-d') . 'T00:00:00Z';
            $endDate = $dateSelectionnee->modify('+1 day')->format('Y-m-d') . 'T00:00:00Z';
    
            $whereCondition = "numer_sta='$num_station_recherche' AND date>='$startDate' AND date<'$endDate'";
            $encodedWhere = urlencode($whereCondition);
    
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?where=$encodedWhere&limit=100";
    
            // Effectuer la requête
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
        
            $data = json_decode($response, true);
        
            // Vérifier si des résultats sont retournés
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                foreach ($data['results'] as $result) {
                    $dateTime = new DateTime($result['date']);
                    $hour = $dateTime->format("H:i");
        
                    // Filtrer pour ne garder que les heures multiples de 3
                    if ($dateTime->format("H") % 3 == 0) {
                        // Récupérer la mesure correspondant au paramètre choisi
                        $weatherCode = isset($result['ww']) ? str_pad($result['ww'], 2, "0", STR_PAD_LEFT) : '00'; 
                        $weatherIcon = $this->getWeatherIcon($weatherCode);
    
                        $mesures[] = [
                            'time' => $hour,
                            'temperature' => isset($result['tc']) ? number_format($result['tc'], 2, '.', '') : null,
                            'vent' => isset($result['ff']) ? number_format($result['ff'], 2, '.', '') : null,
                            'humidite' => isset($result['u']) ? number_format($result['u'], 2, '.', '') : null,
                            'pression' => isset($result['pres']) ? number_format($result['pres'], 2, '.', '') : null,
                            'visibilite' => isset($result['vv']) ? number_format($result['vv'], 2, '.', '') : null,
                            'precipitation' => isset($result['rr24']) ? number_format($result['rr24'], 2, '.', '') : null,
                            'weatherIcon' => $weatherCode,
                            'weatherDescription' => $weatherIcon['description'] 
                        ];                                
                    }
                }
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            // Retourner l'erreur au format JSON
            echo json_encode(['error' => $e->getMessage()]);
            exit; 
        }
        
        return $mesures;
    }
    
    public function getMoyenneMesuresParStationDate($num_station_recherche, $date_recherchee) {
        try {
            // Validation des paramètres
            $dateTimeRecherchee = DateTime::createFromFormat('Y-m-d', $date_recherchee);
            if (!$dateTimeRecherchee) {
                throw new Exception("Format de date invalide. Utiliser 'Y-m-d'.");
            }
    
            // On construit l'URL pour récupérer les données à la date spécifiée
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?refine=numer_sta:$num_station_recherche&refine=date:$date_recherchee";
    
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            $data = json_decode($response, true);
    
            // Initialisation des valeurs de calcul
            $totaux = [
                'temperature' => 0,
                'vent' => 0,
                'humidite' => 0,
                'pression' => 0,
                'visibilite' => 0,
                'precipitation' => 0
            ];
            $comptes = [
                'temperature' => 0,
                'vent' => 0,
                'humidite' => 0,
                'pression' => 0,
                'visibilite' => 0,
                'precipitation' => 0
            ];
    
            // Variables pour stocker les tempMax et tempMin
            $tempMax = null;
            $tempMin = null;

            // Variables pour stocker les pluMax et pluMin
            $pluMax = null;
            $pluMin = null;
    
            // Vérification des résultats
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                foreach ($data['results'] as $result) {
                    // Vérifier et additionner les valeurs valides
                    if (isset($result['tc'])) {
                        $totaux['temperature'] += $result['tc'];
                        $comptes['temperature']++;
    
                        // Calcul des températures max et min
                        if ($tempMax === null || $result['tc'] > $tempMax) {
                            $tempMax = $result['tc'];
                        }
                        if ($tempMin === null || $result['tc'] < $tempMin) {
                            $tempMin = $result['tc'];
                        }
                    }
                    if (isset($result['rr1'])) {
                        $totaux['precipitation'] += $result['rr1'];
                        $comptes['precipitation']++;
    
                        // Calcul des pluviométries max et min
                        if ($pluMax === null || $result['rr1'] > $pluMax) {
                            $pluMax = $result['rr1'];
                        }
                        if ($pluMin === null || $result['rr1'] < $pluMin) {
                            $pluMin = $result['rr1'];
                        }
                    }
                    if (isset($result['ff'])) {
                        $totaux['vent'] += $result['ff'];
                        $comptes['vent']++;
                    }
                    if (isset($result['u'])) {
                        $totaux['humidite'] += $result['u'];
                        $comptes['humidite']++;
                    }
                    if (isset($result['pres'])) {
                        $totaux['pression'] += $result['pres'];
                        $comptes['pression']++;
                    }
                    if (isset($result['vv'])) {
                        $totaux['visibilite'] += $result['vv'];
                        $comptes['visibilite']++;
                    }
                    if (isset($result['rr1'])) {
                        $totaux['precipitation'] += $result['rr1'];
                        $comptes['precipitation']++;
                    }
                }
    
                // Calcul des moyennes
                $moyennes = [];
                foreach ($totaux as $parametre => $somme) {
                    $moyennes[$parametre] = ($comptes[$parametre] > 0) ? round($somme / $comptes[$parametre], 2) : null;
                }
    
                $moyennes['tempMax'] = round($tempMax,2);
                $moyennes['tempMin'] = round($tempMin,2);
                $moyennes['pluMax'] = round($pluMax,2);
                $moyennes['pluMin'] = round($pluMin,2);
    
                return $moyennes;
            } else {
                throw new Exception("Aucune donnée trouvée pour la station et la date spécifiées.");
            }
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    

    public function getMesuresSemaineStation($num_station_recherche, $date_semaine) {
        ob_start();
        
        try {
            // Vérification du format de la semaine
            if (strpos($date_semaine, '-W') !== false) {
                list($annee, $num_semaine) = explode('-W', $date_semaine);
    
                if (is_numeric($annee) && is_numeric($num_semaine)) {
                    $annee = (int) $annee;
                    $num_semaine = (int) $num_semaine;
    
                    $dateTimeSemaine = new DateTime();
                    $dateTimeSemaine->setISODate($annee, $num_semaine);
                    $date_debut_semaine = $dateTimeSemaine->format('Y-m-d');
                    
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => 'Année ou numéro de semaine invalide.']);
                    exit;
                }
            } else {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Format de semaine invalide.']);
                exit;
            }
    
            $mesuresSemaine = [];
    
            for ($i = 0; $i < 7; $i++) {
                $currentDate = (new DateTime($date_debut_semaine))->modify("+$i day")->format('Y-m-d');
                $mesureJour = $this->getMoyenneMesuresParStationDate($num_station_recherche, $currentDate);
                
                // Ajouter tempMax et tempMin en plus des autres mesures
                $mesuresSemaine[$currentDate] = [
                    'tempMax' => $mesureJour['tempMax'],
                    'tempMin' => $mesureJour['tempMin'],
                    'pluMax' => $mesureJour['pluMax'],
                    'pluMin' => $mesureJour['pluMin'],
                    'temperature' => $mesureJour['temperature'],  
                    'vent' => $mesureJour['vent'],  
                    'humidite' => $mesureJour['humidite'],
                    'pression' => $mesureJour['pression'],
                    'visibilite' => $mesureJour['visibilite'],
                    'precipitation' => $mesureJour['precipitation']
                ];
            }
    
            // Nettoie le buffer de sortie et retourne du JSON propre
            ob_end_clean();
            return $mesuresSemaine;
        } catch (Exception $e) {
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
    
    public function calculerMoyenneSemaine($mesuresSemaine) {
        // Variables pour additionner les valeurs de chaque paramètre
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
    
        // Vérifier si des données sont disponibles
        foreach ($mesuresSemaine as $jour => $mesureJour) {
            // Si des mesures sont présentes pour un jour donné, on les additionne
            if ($mesureJour) {
                if (isset($mesureJour['temperature'])) {
                    $totaux['temperature'] += $mesureJour['temperature'];
                    $comptes['temperature']++;
                }
                if (isset($mesureJour['vent'])) {
                    $totaux['vent'] += $mesureJour['vent'];
                    $comptes['vent']++;
                }
                if (isset($mesureJour['humidite'])) {
                    $totaux['humidite'] += $mesureJour['humidite'];
                    $comptes['humidite']++;
                }
                if (isset($mesureJour['pression'])) {
                    $totaux['pression'] += $mesureJour['pression'];
                    $comptes['pression']++;
                }
            }
        }
    
        // Calculer la moyenne pour chaque paramètre
        $moyennesSemaine = [];
        foreach ($totaux as $parametre => $total) {
            // Calculer la moyenne, en évitant la division par zéro
            $moyennesSemaine[$parametre] = ($comptes[$parametre] > 0) ? round($total / $comptes[$parametre], 2) : null;
        }
    
        return $moyennesSemaine;
    }
    
    public function getMesuresMoisStation($num_station_recherche, $date_mois) {
        try {
            $dateTimeMois = DateTime::createFromFormat('Y-m', $date_mois);
            if (!$dateTimeMois) {
                throw new Exception("Format de mois invalide. Utiliser 'Y-m'.");
            }

            $startDate = $dateTimeMois->format('Y-m-01');
            $endDate = $dateTimeMois->modify('+1 month')->format('Y-m-01');

            $mesuresMois = [];
            $currentDate = new DateTime($startDate);

            while ($currentDate->format('Y-m-d') < $endDate) {
                $mesureJour = $this->getMoyenneMesuresParStationDate($num_station_recherche, $currentDate->format('Y-m-d'));
                $mesuresMois[$currentDate->format('Y-m-d')] = $mesureJour;
                $currentDate->modify('+1 day');
            }

            return $mesuresMois;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function calculerMoyenneMois($mesuresMois) {
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

        foreach ($mesuresMois as $jour => $mesureJour) {
            if ($mesureJour) {
                if (isset($mesureJour['temperature'])) {
                    $totaux['temperature'] += $mesureJour['temperature'];
                    $comptes['temperature']++;
                }
                if (isset($mesureJour['vent'])) {
                    $totaux['vent'] += $mesureJour['vent'];
                    $comptes['vent']++;
                }
                if (isset($mesureJour['humidite'])) {
                    $totaux['humidite'] += $mesureJour['humidite'];
                    $comptes['humidite']++;
                }
                if (isset($mesureJour['pression'])) {
                    $totaux['pression'] += $mesureJour['pression'];
                    $comptes['pression']++;
                }
            }
        }

        $moyennesMois = [];
        foreach ($totaux as $parametre => $total) {
            $moyennesMois[$parametre] = ($comptes[$parametre] > 0) ? round($total / $comptes[$parametre], 2) : null;
        }

        return $moyennesMois;
    }
      
    public function getWeatherIcon($weatherCode) {
        switch ($weatherCode) {
            // 00-09 : Phénomènes locaux ou ciel clair
            case '00': return ['icon' => '☀️', 'description' => 'Ciel clair']; 
            case '01': return ['icon' => '🌤️', 'description' => 'Peu de nuages']; 
            case '02': return ['icon' => '🌥️', 'description' => 'Ciel voilé']; 
            case '03': return ['icon' => '☁️', 'description' => 'Nuageux']; 
            case '04': return ['icon' => '🌫️', 'description' => 'Brouillard']; 
            case '05': return ['icon' => '🌁', 'description' => 'Brume']; 
            case '06': return ['icon' => '🌬️', 'description' => 'Vent']; 
            case '07': return ['icon' => '🌀', 'description' => 'Tempête de sable/poussière']; 
            case '08': return ['icon' => '🌪️', 'description' => 'Tourbillon de poussière/sable']; 
            case '09': return ['icon' => '🌈', 'description' => 'Arc-en-ciel']; 
    
            // 10-19 : Brouillard
            case '10': return ['icon' => '🌫️', 'description' => 'Brouillard léger']; 
            case '11': return ['icon' => '🌫️', 'description' => 'Brouillard modéré']; 
            case '12': return ['icon' => '🌫️', 'description' => 'Brouillard dense']; 
            case '13': return ['icon' => '🌫️🌧️', 'description' => 'Brouillard avec bruine']; 
            case '14': return ['icon' => '🌫️🌨️', 'description' => 'Brouillard avec neige']; 
            case '15': return ['icon' => '🌫️⚡', 'description' => 'Brouillard avec orage']; 
            case '16': return ['icon' => '🌫️', 'description' => 'Brouillard persistant']; 
            case '17': return ['icon' => '🌫️', 'description' => 'Brouillard en dissipation']; 
            case '18': return ['icon' => '🌫️💨', 'description' => 'Brouillard avec vent fort']; 
            case '19': return ['icon' => '🌫️❄️', 'description' => 'Brouillard givrant']; 
    
            // 20-29 : Précipitations légères
            case '20': return ['icon' => '🌧️', 'description' => 'Bruine légère']; 
            case '21': return ['icon' => '🌧️', 'description' => 'Bruine']; 
            case '22': return ['icon' => '🌧️', 'description' => 'Bruine forte']; 
            case '23': return ['icon' => '🌧️', 'description' => 'Bruine givrante légère']; 
            case '24': return ['icon' => '🌧️', 'description' => 'Bruine givrante']; 
            case '25': return ['icon' => '🌧️', 'description' => 'Bruine givrante forte']; 
            case '26': return ['icon' => '☔', 'description' => 'Pluie faible']; 
            case '27': return ['icon' => '☔', 'description' => 'Pluie modérée']; 
            case '28': return ['icon' => '☔', 'description' => 'Pluie forte']; 
            case '29': return ['icon' => '🌦️', 'description' => 'Pluie intermittente']; 
    
            // 30-39 : Précipitations solides
            case '30': return ['icon' => '🌨️', 'description' => 'Neige faible']; 
            case '31': return ['icon' => '🌨️', 'description' => 'Neige modérée']; 
            case '32': return ['icon' => '🌨️', 'description' => 'Neige forte']; 
            case '33': return ['icon' => '☃️', 'description' => 'Tempête de neige']; 
            case '34': return ['icon' => '🌨️', 'description' => 'Neige fondue']; 
            case '35': return ['icon' => '🌨️❄️', 'description' => 'Chutes de neige intermittentes']; 
            case '36': return ['icon' => '🌨️❄️', 'description' => 'Neige continue']; 
            case '37': return ['icon' => '🌨️❄️', 'description' => 'Grésil']; 
            case '38': return ['icon' => '🧊', 'description' => 'Grêle']; 
            case '39': return ['icon' => '🌨️❄️', 'description' => 'Averses de neige']; 
    
            // 40-49 : Mélange pluie/neige
            case '40': return ['icon' => '🌧️❄️', 'description' => 'Pluie et neige faible']; 
            case '41': return ['icon' => '🌧️❄️', 'description' => 'Pluie et neige modérée']; 
            case '42': return ['icon' => '🌧️❄️', 'description' => 'Pluie et neige forte']; 
            case '43': return ['icon' => '🌧️❄️', 'description' => 'Pluie et neige intermittente']; 
            case '44': return ['icon' => '🌧️❄️', 'description' => 'Pluie et neige continue']; 
            case '45': return ['icon' => '🌨️💧', 'description' => 'Bruine/neige mélangée']; 
            case '46': return ['icon' => '❄️💧', 'description' => 'Neige et pluie verglaçante']; 
            case '47': return ['icon' => '🌧️❄️', 'description' => 'Pluie et grêle']; 
            case '48': return ['icon' => '☔❄️', 'description' => 'Grésil/neige mélangée']; 
            case '49': return ['icon' => '🌧️❄️', 'description' => 'Mélange complexe']; 
    
            // 50-59 : Pluies régulières
            case '50': return ['icon' => '☔', 'description' => 'Pluie faible']; 
            case '51': return ['icon' => '☔', 'description' => 'Pluie modérée']; 
            case '52': return ['icon' => '☔', 'description' => 'Pluie forte']; 
            case '53': return ['icon' => '🌧️', 'description' => 'Pluie intermittente faible']; 
            case '54': return ['icon' => '🌧️', 'description' => 'Pluie intermittente modérée']; 
            case '55': return ['icon' => '🌧️', 'description' => 'Pluie intermittente forte']; 
            case '56': return ['icon' => '☔', 'description' => 'Pluie verglaçante légère']; 
            case '57': return ['icon' => '☔', 'description' => 'Pluie verglaçante modérée']; 
            case '58': return ['icon' => '☔', 'description' => 'Pluie verglaçante forte']; 
            case '59': return ['icon' => '🌦️', 'description' => 'Pluie avec soleil']; 
    
            // 60-69 : Précipitations orageuses
            case '60': return ['icon' => '⛈️', 'description' => 'Orage avec pluie faible']; 
            case '61': return ['icon' => '⛈️', 'description' => 'Orage avec pluie modérée']; 
            case '62': return ['icon' => '⛈️', 'description' => 'Orage avec pluie forte']; 
            case '63': return ['icon' => '⛈️', 'description' => 'Orage sans pluie']; 
            case '64': return ['icon' => '⛈️❄️', 'description' => 'Orage avec neige']; 
            case '65': return ['icon' => '⛈️', 'description' => 'Orage avec grêle']; 
            case '66': return ['icon' => '⛈️', 'description' => 'Orage intermittent']; 
            case '67': return ['icon' => '⛈️', 'description' => 'Orage continu']; 
            case '68': return ['icon' => '⛈️', 'description' => 'Orage avec vent fort']; 
            case '69': return ['icon' => '⛈️', 'description' => 'Orage violent']; 
    
            // 70-79 : Précipitations fortes
            case '70': return ['icon' => '🌨️', 'description' => 'Neige forte']; 
            case '71': return ['icon' => '🌨️❄️', 'description' => 'Chutes de neige continue']; 
            case '72': return ['icon' => '🌨️❄️', 'description' => 'Rafales de neige']; 
            case '73': return ['icon' => '☃️', 'description' => 'Blizzard']; 
            case '74': return ['icon' => '🌨️❄️', 'description' => 'Fortes précipitations de neige']; 
            case '75': return ['icon' => '🌨️❄️', 'description' => 'Neige avec rafales de vent']; 
            case '76': return ['icon' => '🌨️❄️', 'description' => 'Neige intermittente']; 
            case '77': return ['icon' => '☃️❄️', 'description' => 'Fortes tempêtes de neige']; 
            case '78': return ['icon' => '🌨️❄️', 'description' => 'Averses de neige']; 
            case '79': return ['icon' => '🌨️', 'description' => 'Conditions hivernales intenses']; 
    
            // 80-89 : Orages violents
            case '80': return ['icon' => '⛈️', 'description' => 'Orage faible']; 
            case '81': return ['icon' => '⛈️', 'description' => 'Orage modéré']; 
            case '82': return ['icon' => '⚡️', 'description' => 'Orage fort']; 
            case '83': return ['icon' => '🌩️', 'description' => 'Éclairs sans pluie']; 
            case '84': return ['icon' => '🌩️❄️', 'description' => 'Orage avec neige']; 
            case '85': return ['icon' => '⛈️❄️', 'description' => 'Tempête orageuse']; 
            case '86': return ['icon' => '⛈️🌪️', 'description' => 'Orage avec tornade']; 
            case '87': return ['icon' => '⛈️🌬️', 'description' => 'Orage avec vents forts']; 
            case '88': return ['icon' => '⛈️❄️', 'description' => 'Orage neigeux']; 
            case '89': return ['icon' => '⛈️', 'description' => 'Orage extrême']; 
    
            // 90-99 : Phénomènes extrêmes
            case '90': return ['icon' => '🌪️', 'description' => 'Tornade']; 
            case '91': return ['icon' => '💨', 'description' => 'Vent violent']; 
            case '92': return ['icon' => '💨', 'description' => 'Rafales de vent']; 
            case '93': return ['icon' => '🌀', 'description' => 'Cyclone tropical']; 
            case '94': return ['icon' => '🌀', 'description' => 'Ouragan']; 
            case '95': return ['icon' => '⛈️', 'description' => 'Tempête orageuse violente']; 
            case '96': return ['icon' => '🌩️', 'description' => 'Fortes éclairs']; 
            case '97': return ['icon' => '⛈️', 'description' => 'Orages dispersés']; 
            case '98': return ['icon' => '🌩️', 'description' => 'Éclairs isolés']; 
            case '99': return ['icon' => '⛈️', 'description' => 'Orage extrême']; 
    
            // Par défaut
            default: return ['icon' => '❓', 'description' => 'Code inconnu']; 
        }
    }    

    public function getMesuresAnneeStation($num_station_recherche, $date_annee) {
        try {
            $dateTimeAnnee = DateTime::createFromFormat('Y', $date_annee);
            if (!$dateTimeAnnee) {
                throw new Exception("Format d'année invalide. Utiliser 'Y'.");
            }

            $startDate = $dateTimeAnnee->format('Y-01-01');
            $endDate = $dateTimeAnnee->modify('+1 year')->format('Y-01-01');

            $mesuresAnnee = [];
            $currentDate = new DateTime($startDate);

            while ($currentDate->format('Y-m-d') < $endDate) {
                $mesureJour = $this->getMoyenneMesuresParStationDate($num_station_recherche, $currentDate->format('Y-m-d'));
                $mesuresAnnee[$currentDate->format('Y-m-d')] = $mesureJour;
                $currentDate->modify('+1 day');
            }

            return $mesuresAnnee;
        } catch (Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function calculerMoyenneAnnee($mesuresAnnee) {
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

        foreach ($mesuresAnnee as $jour => $mesureJour) {
            if ($mesureJour) {
                if (isset($mesureJour['temperature'])) {
                    $totaux['temperature'] += $mesureJour['temperature'];
                    $comptes['temperature']++;
                }
                if (isset($mesureJour['vent'])) {
                    $totaux['vent'] += $mesureJour['vent'];
                    $comptes['vent']++;
                }
                if (isset($mesureJour['humidite'])) {
                    $totaux['humidite'] += $mesureJour['humidite'];
                    $comptes['humidite']++;
                }
                if (isset($mesureJour['pression'])) {
                    $totaux['pression'] += $mesureJour['pression'];
                    $comptes['pression']++;
                }
            }
        }

        $moyennesAnnee = [];
        foreach ($totaux as $parametre => $total) {
            $moyennesAnnee[$parametre] = ($comptes[$parametre] > 0) ? round($total / $comptes[$parametre], 2) : null;
        }

        return $moyennesAnnee;
    }
}
?>
