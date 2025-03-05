<?php
require_once __DIR__ . '/../model/ModelClimatique.php';

class ControllerClimatique {
    private $model;

    public function __construct() {
        $this->model = new ModelClimatique();
    }

    //Méthode pour obtenir les mesures moyennes par date et paramètre
    public function getMesuresMoyennesParDebutEtFin($date_debut, $date_fin, $parametre) {
        return $this->model->getMesuresMoyennesParDebutEtFin($date_debut, $date_fin, $parametre);
    }

    //Méthode pour calculer les variations et retourner les résultats en format GeoJSON
    public function getVariationsEnGeoJSON($date_debut, $date_fin, $parametre) {
        //Récupérer les résultats des moyennes et variations
        $resultats = $this->model->getMesuresMoyennesParDebutEtFin($date_debut, $date_fin, $parametre);

        // Construire les données GeoJSON
        $features = [];
        foreach ($resultats as $station_id => $data) {
            $longitude = $data['longitude'] ?? null;
            $latitude = $data['latitude'] ?? null;

            if ($longitude !== null && $latitude !== null) {
                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$longitude, $latitude]
                    ],
                    'properties' => [
                        'station' => $station_id,
                        'nom_station' => $data['nom_station'] ?? null,
                        'moyenne_debut' => $data['moyenne_debut'] ?? null,
                        'moyenne_fin' => $data['moyenne_fin'] ?? null,
                        'variation' => $data['variation'] ?? null
                    ]
                ];
            } else {
                error_log("Station $station_id n'a pas de coordonnées valides.");
            }
        }

        return json_encode([
            'type' => 'FeatureCollection',
            'features' => $features
        ]);
    }   
}
?>
