<?php
require_once __DIR__ . '/../model/Model.php';

class ModelMeteotheque {
    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }

    public function ajouterStationMeteotheque($user_id, $num_station) {
        try {
            $query = $this->pdo->query("SELECT 1 FROM stations WHERE num_station = '$num_station'");
            if (!$query->fetch()) {
                throw new Exception("La station n'existe pas dans la table stations");
            }

            $query = "INSERT INTO meteotheque (user_id, num_station, creation_date) VALUES (?, ?, NOW())";
            $stmt = $this->pdo->prepare($query);
            if (!$stmt->execute([$user_id, $num_station])) {
                throw new Exception("Erreur lors de l'insertion dans la base de données");
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function supprimerStationMeteotheque($user_id, $num_station) {
        try {
            $query = "UPDATE meteotheque SET num_station = NULL WHERE num_station = :num_station AND user_id = :user_id";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':num_station', $num_station, PDO::PARAM_INT);
            if (!$stmt->execute()) {
                throw new Exception("Erreur lors de la suppression dans la base de données");
            }
            return true;
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function estFavoris($user_id, $num_station) {
        try {
            $query = "SELECT * FROM meteotheque WHERE user_id = :user_id AND num_station = :num_station";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->bindParam(':num_station', $num_station, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    public function getFavoriteStations($user_id) {
        $query = "SELECT s.* FROM stations s
                  JOIN meteotheque m ON s.num_station = m.num_station
                  WHERE m.user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getDernieresMesuresParStation($num_station_recherche) {
        $derniereMesure = null;
    
        try {
            // Construire l'URL avec les filtres pour obtenir la dernière mise à jour
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?limit=1&order_by=-date&refine=numer_sta:$num_station_recherche";
    
            // Effectuer la requête
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            $data = json_decode($response, true);
    
            //On vérifie si des résultats sont retournés
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                // Extraire la première (et seule) mesure
                $result = $data['results'][0];
    
                // Récupérer les mesures 
                $derniereMesure = [
                    'date' => $result['date'] ?? null,
                    'temperature' => $result['tc'] ?? null, 
                    'pression' => $result['pres'] ?? null, 
                    'vent' => $result['ff'] ?? null, 
                    'humidite' => $result['u'] ?? null, 
                    'visibilite' => $result['vv'] ?? null, 
                    'precipitation' => $result['rr24'] ?? null 
                ];
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return $derniereMesure;
    }
    public function getMesuresParStationEtDate($num_station_recherche, $date) {
        $derniereMesure = null;
    
        try {
            // Construire l'URL avec les filtres pour obtenir la dernière mise à jour
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?limit=1&order_by=-date&refine=numer_sta:$num_station_recherche";
    
            // Effectuer la requête
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            $data = json_decode($response, true);
    
            //On vérifie si des résultats sont retournés
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                // Extraire la première (et seule) mesure
                $result = $data['results'][0];
    
                // Récupérer les mesures 
                $derniereMesure = [
                    'date' => $result['date'] ?? null,
                    'temperature' => $result['tc'] ?? null, 
                    'pression' => $result['pres'] ?? null, 
                    'vent' => $result['ff'] ?? null, 
                    'humidite' => $result['u'] ?? null, 
                    'visibilite' => $result['vv'] ?? null, 
                    'precipitation' => $result['rr24'] ?? null 
                ];
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return $derniereMesure;
    }
    public function getMesuresParStation($num_station_recherche, $date = null) {
        $mesures = [];
        
        try {
            if ($date) {
                // Si une date est fournie, on l'utilise directement
                $dateDerniereMesure = new DateTime($date);
            } else {
                // Sinon on récupère la dernière date disponible
                $derniereMesure = $this->getDernieresMesuresParStation($num_station_recherche);
                if (!$derniereMesure || !isset($derniereMesure['date'])) {
                    throw new Exception("Impossible d'obtenir la dernière mesure pour la station $num_station_recherche.");
                }
                $dateDerniereMesure = new DateTime($derniereMesure['date']);
            }
    
            // Réinitialise l'heure à 00:00 pour éviter des erreurs de format
            $dateDerniereMesure->setTime(0, 0);
            $dateStr = $dateDerniereMesure->format('Y-m-d');
            
            // Construire l'URL avec la date formatée
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?refine=numer_sta:$num_station_recherche&refine=date:$dateStr";
            
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données pour la station $num_station_recherche.");
            }
    
            $data = json_decode($response, true);
    
            if (isset($data['results']) && !empty($data['results'])) {
                foreach ($data['results'] as $result) {
                    $dateTime = new DateTime($result['date']);
                    
                    // Filtrer les heures multiples de 3
                    if ($dateTime->format("H") % 3 == 0) {
                        $mesures[$dateTime->format("H:i")] = [
                            'temperature' => $this->formatValue($result['tc']),
                            'pression' => $this->formatValue($result['pres'] ?? null),
                            'vent' => $this->formatValue($result['ff'] ?? null),
                            'humidite' => $this->formatValue($result['u'] ?? null),
                            'visibilite' => $this->formatValue($result['vv'] ?? null),
                            'precipitation' => $this->formatValue($result['rr24'] ?? null)
                        ];
                    }
                }
            } else {
                throw new Exception("Aucune donnée trouvée pour la station $num_station_recherche.");
            }
    
        } catch (Exception $e) {
            // Journaliser l'erreur
            error_log("Erreur: " . $e->getMessage());
            return []; // Retourner un tableau vide en cas d'erreur
        }
        
        return $mesures;
    }
    
    
    
    private function formatValue($value) {
        return $value !== null ? round(floatval($value), 2) : null;
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
                'precipitation' => 0
            ];
            $comptes = [
                'temperature' => 0,
                'vent' => 0,
                'humidite' => 0,
                'precipitation' => 0
            ];

            // Vérification des résultats
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                foreach ($data['results'] as $result) {
                    // Vérifier et additionner les valeurs valides
                    if (isset($result['tc'])) {
                        $totaux['temperature'] += $result['tc'];
                        $comptes['temperature']++;
                    }
                    if (isset($result['ff'])) {
                        $totaux['vent'] += $result['ff'];
                        $comptes['vent']++;
                    }
                    if (isset($result['u'])) {
                        $totaux['humidite'] += $result['u'];
                        $comptes['humidite']++;
                    }
                    if (isset($result['rr24'])) {
                        $totaux['precipitation'] += $result['rr24'];
                        $comptes['precipitation']++;
                    }
                }

                // Calcul des moyennes
                $moyennes = [];
                foreach ($totaux as $parametre => $somme) {
                    $moyennes[$parametre] = ($comptes[$parametre] > 0) ? round($somme / $comptes[$parametre], 2) : null;
                }

                return $moyennes;
            } else {
                throw new Exception("Aucune donnée trouvée pour la station ou la date spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
            return null;
        }
    }
    
    public function getTemperaturesMaxMinLast7Days($num_station_recherche, $selectedDate = null) {
        $weatherData = [];
    
        try {
            // Si l'utilisateur sélectionne une date, on l'utilise, sinon on prend la dernière mesure
            if ($selectedDate) {
                $dateDerniereMesure = new DateTime($selectedDate);
            } else {
                $derniereMesure = $this->getDernieresMesuresParStation($num_station_recherche);
                if (!$derniereMesure || !isset($derniereMesure['date'])) {
                    throw new Exception("Impossible d'obtenir la dernière mesure.");
                }
                $dateDerniereMesure = new DateTime($derniereMesure['date']);
            }
    
            // Fixer l'heure à minuit pour éviter les erreurs
            $dateDerniereMesure->setTime(0, 0);
            $dateSeptJoursAvant = (clone $dateDerniereMesure)->modify('-7 days');
    
            // Construire l'URL pour récupérer les données des 7 derniers jours
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?"
                 . "refine=numer_sta:$num_station_recherche"
                 . "&where=date>='{$dateSeptJoursAvant->format('Y-m-d')}'"
                 . "&where=date<'{$dateDerniereMesure->format('Y-m-d')}'"
                 . "&order_by=date&limit=100";
    
            // Récupérer les données depuis l'API
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            $data = json_decode($response, true);
    
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                foreach ($data['results'] as $result) {
                    // Extraire la date sans l'heure
                    $date = substr($result['date'], 0, 10);
                    $temperature = round($result['tc'], 1);
                    $weatherCode = $result['ww'];
    
                    if (!isset($weatherData[$date])) {
                        $weatherData[$date] = [
                            'max' => $temperature,
                            'min' => $temperature,
                            'weather' => $weatherCode
                        ];
                    } else {
                        if ($temperature > $weatherData[$date]['max']) {
                            $weatherData[$date]['max'] = $temperature;
                        }
                        if ($temperature < $weatherData[$date]['min']) {
                            $weatherData[$date]['min'] = $temperature;
                        }
                        $weatherData[$date]['weather'] = $weatherCode;
                    }
                }
    
                ksort($weatherData); 
                return $weatherData;
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return [];
    }   

    public function getWeatherIcon($weatherCode) {
        switch ($weatherCode) {
            // 00-09 : Phénomènes locaux ou ciel clair
            case '00': return '☀️'; // Ciel clair
            case '01': return '🌤️'; // Peu de nuages
            case '02': return '🌥️'; // Ciel voilé
            case '03': return '☁️'; // Nuageux
            case '04': return '🌫️'; // Brouillard
            case '05': return '🌁'; // Brume
            case '06': return '🌬️'; // Vent
            case '07': return '🌀'; // Tempête de sable/poussière
            case '08': return '🌪️'; // Tourbillon de poussière/sable
            case '09': return '🌈'; // Arc-en-ciel
    
            // 10-19 : Brouillard
            case '10': return '🌫️'; // Brouillard léger
            case '11': return '🌫️'; // Brouillard modéré
            case '12': return '🌫️'; // Brouillard dense
            case '13': return '🌫️🌧️'; // Brouillard avec bruine
            case '14': return '🌫️🌨️'; // Brouillard avec neige
            case '15': return '🌫️⚡'; // Brouillard avec orage
            case '16': return '🌫️'; // Brouillard persistant
            case '17': return '🌫️'; // Brouillard en dissipation
            case '18': return '🌫️💨'; // Brouillard avec vent fort
            case '19': return '🌫️❄️'; // Brouillard givrant
    
            // 20-29 : Précipitations légères
            case '20': return '🌧️'; // Bruine légère
            case '21': return '🌧️'; // Bruine
            case '22': return '🌧️'; // Bruine forte
            case '23': return '🌧️'; // Bruine givrante légère
            case '24': return '🌧️'; // Bruine givrante
            case '25': return '🌧️'; // Bruine givrante forte
            case '26': return '☔'; // Pluie faible
            case '27': return '☔'; // Pluie modérée
            case '28': return '☔'; // Pluie forte
            case '29': return '🌦️'; // Pluie intermittente
    
            // 30-39 : Précipitations solides
            case '30': return '🌨️'; // Neige faible
            case '31': return '🌨️'; // Neige modérée
            case '32': return '🌨️'; // Neige forte
            case '33': return '☃️'; // Tempête de neige
            case '34': return '🌨️'; // Neige fondue
            case '35': return '🌨️❄️'; // Chutes de neige intermittentes
            case '36': return '🌨️❄️'; // Neige continue
            case '37': return '🌨️'; // Grésil
            case '38': return '🧊'; // Grêle
            case '39': return '🌨️❄️'; // Averses de neige
    
            // 40-49 : Mélange pluie/neige
            case '40': return '🌧️❄️'; // Pluie et neige faible
            case '41': return '🌧️❄️'; // Pluie et neige modérée
            case '42': return '🌧️❄️'; // Pluie et neige forte
            case '43': return '🌧️❄️'; // Pluie et neige intermittente
            case '44': return '🌧️❄️'; // Pluie et neige continue
            case '45': return '🌨️💧'; // Bruine/neige mélangée
            case '46': return '❄️💧'; // Neige et pluie verglaçante
            case '47': return '🌧️❄️'; // Pluie et grêle
            case '48': return '☔❄️'; // Grésil/neige mélangée
            case '49': return '🌧️❄️'; // Mélange complexe
    
            // 50-59 : Pluies régulières
            case '50': return '☔'; // Pluie faible
            case '51': return '☔'; // Pluie modérée
            case '52': return '☔'; // Pluie forte
            case '53': return '🌧️'; // Pluie intermittente faible
            case '54': return '🌧️'; // Pluie intermittente modérée
            case '55': return '🌧️'; // Pluie intermittente forte
            case '56': return '☔'; // Pluie verglaçante légère
            case '57': return '☔'; // Pluie verglaçante modérée
            case '58': return '☔'; // Pluie verglaçante forte
            case '59': return '🌦️'; // Pluie avec soleil
    
            // 60-69 : Précipitations orageuses
            case '60': return '⛈️'; // Orage avec pluie faible
            case '61': return '⛈️'; // Orage avec pluie modérée
            case '62': return '⛈️'; // Orage avec pluie forte
            case '63': return '⛈️'; // Orage sans pluie
            case '64': return '⛈️❄️'; // Orage avec neige
            case '65': return '⛈️'; // Orage avec grêle
            case '66': return '⛈️'; // Orage intermittent
            case '67': return '⛈️'; // Orage continu
            case '68': return '⛈️'; // Orage avec vent fort
            case '69': return '⛈️'; // Orage violent
    
            // 70-79 : Précipitations fortes
            case '70': return '🌨️'; // Neige forte
            case '71': return '🌨️❄️'; // Chutes de neige continue
            case '72': return '🌨️❄️'; // Rafales de neige
            case '73': return '☃️'; // Blizzard
            case '74': return '🌨️❄️'; // Fortes précipitations de neige
            case '75': return '🌨️❄️'; // Neige avec rafales de vent
            case '76': return '🌨️❄️'; // Neige intermittente
            case '77': return '☃️❄️'; // Fortes tempêtes de neige
            case '78': return '🌨️❄️'; // Averses de neige
            case '79': return '🌨️'; // Conditions hivernales intenses
    
            // 80-89 : Orages violents
            case '80': return '⛈️'; // Orage faible
            case '81': return '⛈️'; // Orage modéré
            case '82': return '⚡️'; // Orage fort
            case '83': return '🌩️'; // Éclairs sans pluie
            case '84': return '🌩️❄️'; // Orage avec neige
            case '85': return '⛈️❄️'; // Tempête orageuse
            case '86': return '⛈️🌪️'; // Orage avec tornade
            case '87': return '⛈️🌬️'; // Orage avec vents forts
            case '88': return '⛈️❄️'; // Orage neigeux
            case '89': return '⛈️'; // Orage extrême
    
            // 90-99 : Phénomènes extrêmes
            case '90': return '🌪️'; // Tornade
            case '91': return '💨'; // Vent violent
            case '92': return '💨'; // Rafales de vent
            case '93': return '🌀'; // Cyclone tropical
            case '94': return '🌀'; // Ouragan
            case '95': return '⛈️'; // Tempête orageuse violente
            case '96': return '🌩️'; // Fortes éclairs
            case '97': return '⛈️'; // Orages dispersés
            case '98': return '🌩️'; // Éclairs isolés
            case '99': return '⛈️'; // Orage extrême
    
            // Par défaut
            default: return '❓'; 
        }
    }
    public function getMesuresParStationDateHeure($num_station_recherche, $date_recherchee, $heure_recherchee) {
        $mesures = [];
    
        try {
            // Validation des paramètres
            $dateTimeRecherchee = DateTime::createFromFormat('Y-m-d H:i', "$date_recherchee $heure_recherchee");
            if (!$dateTimeRecherchee) {
                throw new Exception("Format de date ou heure invalide. Utiliser 'Y-m-d' pour la date et 'H:i' pour l'heure.");
            }
    
            // On construit l'URL pour récupérer les données à la date spécifiée
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?refine=numer_sta:$num_station_recherche&refine=date:$date_recherchee";
    
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            $data = json_decode($response, true);
    
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                foreach ($data['results'] as $result) {
                    $dateTime = new DateTime($result['date']);
                    $hour = $dateTime->format("H:i");
    
                    // Filtrer pour ne garder que la date et l'heure recherchées
                    if ($dateTime->format("Y-m-d H:i") === $dateTimeRecherchee->format("Y-m-d H:i")) {
                        $mesures[$hour] = [
                            'temperature' => round($result['tc'], 2),
                            'pression' => $result['pres'] ?? null,
                            'vent' => $result['ff'] ?? null,
                            'humidite' => $result['u'] ?? null,
                            'visibilite' => $result['vv'] ?? null,
                            'precipitation' => $result['rr24'] ?? null,
                        ];
                    }
                }
    
                if (empty($mesures)) {
                    throw new Exception("Aucune donnée trouvée pour la date et l'heure spécifiées : $date_recherchee $heure_recherchee.");
                }
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return $mesures;
    }  

    public function updatePublicationStatus($userId, $publicationStatus) {
        $stmt = $this->pdo->prepare("UPDATE meteotheque SET publication = :publication WHERE user_id = :user_id");
        $stmt->bindParam(':publication', $publicationStatus, PDO::PARAM_INT);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    
        $success = $stmt->execute();
    
        if (!$success) {
            $errorInfo = $stmt->errorInfo();
            throw new Exception("Erreur lors de la mise à jour de la publication: " . $errorInfo[2]);
        }
    
        return $success;
    }

    public function getPublicationStatus($userId) {
        $query = "SELECT publication FROM meteotheque WHERE user_id = ?";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(); 
        return $result ? $result['publication'] : null; 
    }    

    public function getMeteothequeById($id) {
        $sql = "SELECT m.meteotheque_id, u.prenom, u.nom 
                FROM meteotheque AS m
                JOIN users AS u ON m.user_id = u.user_id
                WHERE m.meteotheque_id = :id AND m.publication = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC); 
    }
    // Récupérer les alertes de l'utilisateur
    public function getAlertsByUser($userId) {
        $sql = "SELECT * FROM alertes WHERE user_id = :user_id ORDER BY date_alerte DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //Méthode pour supprimer une alerte
    public function supprimerAlerte($alert_id) {
        $query = "DELETE FROM alertes WHERE alert_id = :alert_id";
        $stmt = $this->pdo->prepare($query);
    
        $stmt->bindParam(':alert_id', $alert_id, PDO::PARAM_INT);
    
        return $stmt->execute(); 
    }
    
}