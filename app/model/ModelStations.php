<?php
require_once __DIR__ . '/../model/Model.php';

class ModelStations {
    // Méthode pour insérer des données dans la base de données
    public function insertStations($num_station, $nom, $latitude, $longitude, $altitude, $libgeo, $codegeo, $nom_epci, $code_epci, $nom_dept, $code_dept, $nom_reg, $code_reg) {
        $pdo = Model::getInstance()->getPdo();

        $stmt = $pdo->prepare("INSERT IGNORE INTO stations (num_station, nom, latitude, longitude, altitude, libgeo, codegeo, nom_epci, code_epci, nom_dept, code_dept, nom_reg, code_reg) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bindParam(1, $num_station);
        $stmt->bindParam(2, $nom);
        $stmt->bindParam(3, $latitude);
        $stmt->bindParam(4, $longitude);
        $stmt->bindParam(5, $altitude);
        $stmt->bindParam(6, $libgeo);
        $stmt->bindParam(7, $codegeo);
        $stmt->bindParam(8, $nom_epci);
        $stmt->bindParam(9, $code_epci);
        $stmt->bindParam(10, $nom_dept);
        $stmt->bindParam(11, $code_dept);
        $stmt->bindParam(12, $nom_reg);
        $stmt->bindParam(13, $code_reg);

        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion : " . implode(", ", $stmt->errorInfo()));
        }
    }

    //Méthode pour récupérer les stations depuis l'API
    public function recupererStationsDepuisAPI() {
        $pageSize = 100; 
        $stations = []; 
        try {
            // Première requête pour obtenir le nombre total d'enregistrements
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?limit=$pageSize&offset=0";
            $response = file_get_contents($url);
            $data = json_decode($response, true);

            // Vérifier si $data et 'total_count' sont définis
            if (isset($data['total_count'])) {
                $totalCount = $data['total_count']; 
            } else {
                echo "Impossible de récupérer le nombre total d'enregistrements.";
                return $stations;
            }
            // Boucle de pagination pour récupérer et insérer toutes les stations
            for ($offset = 0; $offset < $totalCount; $offset += $pageSize) {
                // Construire l'URL avec le paramètre de pagination
                $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?limit=$pageSize&offset=$offset";
                
                // Envoyer la requête et récupérer la réponse JSON
                $response = file_get_contents($url);
                $data = json_decode($response, true);
                // Vérifier si des résultats sont disponibles
                if (isset($data['results']) && is_array($data['results'])) {
                    foreach ($data['results'] as $result) {
                        // Ajouter chaque station récupérée dans le tableau
                        $stations[] = [
                            'num_station' => $result['numer_sta'] ?? null,
                            'nom' => $result['nom'] ?? null,
                            'latitude' => $result['latitude'] ?? null,
                            'longitude' => $result['longitude'] ?? null,
                            'altitude' => $result['altitude'] ?? null,
                            'libgeo' => $result['libgeo'] ?? null,
                            'codegeo' => $result['codegeo'] ?? null,
                            'nom_epci' => $result['nom_epci'] ?? null,
                            'code_epci' => $result['code_epci'] ?? null,
                            'nom_dept' => $result['nom_dept'] ?? null,
                            'code_dept' => $result['code_dep'] ?? null,
                            'nom_reg' => $result['nom_reg'] ?? null,
                            'code_reg' => $result['code_reg'] ?? null,
                            'date' => $result['date'] ?? null
                        ];
                    }
                } else {
                    break;
                }
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }

        return $stations; 
    }
    // Récupérer toutes les stations
    public function getAllStations() {
        $pdo = Model::getInstance()->getPdo();
        $sql = "SELECT num_station, nom, latitude, longitude, altitude, libgeo, codegeo, nom_epci, code_epci, nom_dept, code_dept, nom_reg, code_reg 
                FROM stations 
                WHERE latitude IS NOT NULL AND longitude IS NOT NULL";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function getNomEtNumStations() {
        $stations = [];
        try {
            $pdo = Model::getInstance()->getPdo();
            $query = $pdo->query("SELECT num_station, nom FROM stations ORDER BY nom ASC");
            $stations = $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
        return $stations;
    }
    
    public function getStationByNum($num_station) {
        $pdo = Model::getInstance()->getPdo();
        $query = "SELECT * FROM stations WHERE num_station = :num_station";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':num_station', $num_station, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function getDerniereMesureParStation($num_station_recherche) {
        $derniereDate = null;
    
        try {
            // Construire l'URL avec les filtres pour obtenir la dernière mise à jour
            $url = "https://public.opendatasoft.com/api/explore/v2.1/catalog/datasets/donnees-synop-essentielles-omm/records?limit=1&order_by=-date&refine=numer_sta:$num_station_recherche";
    
            // Effectuer la requête
            $response = file_get_contents($url);
            if ($response === false) {
                throw new Exception("Erreur lors de la récupération des données depuis l'API.");
            }
    
            // Décoder la réponse JSON
            $data = json_decode($response, true);
    
            // Vérifier si des résultats sont retournés
            if (isset($data['results']) && is_array($data['results']) && count($data['results']) > 0) {
                // Extraire la première (et seule) mesure
                $result = $data['results'][0];
    
                // Vérifier si la clé `date` existe et la retourner
                if (isset($result['date'])) {
                    $derniereDate = $result['date'];
                } else {
                    throw new Exception("La date n'est pas disponible dans les résultats.");
                }
            } else {
                throw new Exception("Aucune donnée trouvée pour la station spécifiée.");
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    
        return $derniereDate;
    }    
}

?>
