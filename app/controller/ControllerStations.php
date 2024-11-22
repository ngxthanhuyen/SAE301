<?php
include_once '../model/Model.php';
include_once '../model/ModelStations.php';


class ControllerStations {
    private $modelStations;

    public function __construct() {
        $this->modelStations = new ModelStations();
    }

    public function recupererStationsDepuisAPI() {
        //Taille de la page imposée par l'API
        $pageSize = 100; 
        
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
                return;
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
                        // Insérer les données dans la base de données
                        $this->modelStations->insertStations(
                            $result['numer_sta'] ?? null,
                            $result['nom'] ?? null,
                            $result['latitude'] ?? null,
                            $result['longitude'] ?? null,
                            $result['altitude'] ?? null,
                            $result['libgeo'] ?? null,
                            $result['codegeo'] ?? null,
                            $result['nom_epci'] ?? null,
                            $result['code_epci'] ?? null,
                            $result['nom_dept'] ?? null,
                            $result['code_dep'] ?? null,
                            $result['nom_reg'] ?? null,
                            $result['code_reg'] ?? null
                        );
                    }
                } else {
                    break;
                }
            }
        } catch (Exception $e) {
            echo "Erreur : " . $e->getMessage();
        }
    }    
}
$controller = new ControllerStations();
$controller->recupererStationsDepuisAPI();

?>
