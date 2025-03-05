<?php
include_once '../model/ModelStations.php';

class ControllerStations {
    private $modelStations;

    public function __construct() {
        $this->modelStations = new ModelStations();
    }

    // Méthode pour insérer des stations dans la base de données
    public function insererStationsDansBD($stations) {
        foreach ($stations as $station) {
            $this->modelStations->insertStations(
                $station['num_station'],
                $station['nom'],
                $station['latitude'],
                $station['longitude'],
                $station['altitude'],
                $station['libgeo'],
                $station['codegeo'],
                $station['nom_epci'],
                $station['code_epci'],
                $station['nom_dept'],
                $station['code_dept'],
                $station['nom_reg'],
                $station['code_reg']
            );
        }
    }
    // Récupérer toutes les stations depuis la base de données
    public function getStations() {
        $stations = $this->modelStations->getAllStations();
        return $stations;
    }
    public function afficherOptionsStations() {
        // Récupère les stations depuis le modèle
        $stations = $this->modelStations->getNomEtNumStations();
    
        // Retourner les stations sous forme de tableau
        return $stations;
    }
    

    public function getStationByNum($num_station) {
        $station = $this->modelStations->getStationByNum($num_station);
        if ($station) {
            return $station;
        } else {
            return "Station non trouvée";
        }
    }    
    // Ajouter dans la classe ControllerStations
    public function getDerniereMesureParStation($num_station) {
        // Utiliser la méthode correspondante du modèle
        $derniereDate = $this->modelStations->getDerniereMesureParStation($num_station);
        return $derniereDate;
    }
    public function getStationByName($name) {
        $pdo = Model::getInstance()->getPdo();
        $query = "SELECT * FROM stations WHERE nom LIKE :name";
        $stmt = $pdo->prepare($query);
        $likeName = "%" . $name . "%";
        $stmt->bindParam(':name', $likeName, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }    
}

?>