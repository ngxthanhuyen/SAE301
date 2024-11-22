<?php
require_once __DIR__ . '/../model/Model.php';

class ModelStations {
    public function insertStations($num_station, $nom, $latitude, $longitude, $altitude, $libgeo, $codegeo, $nom_epci, $code_epci, $nom_dept, $code_dept, $nom_reg, $code_reg) {
        $pdo = Model::getInstance()->getPdo(); // Utilisation de getInstance pour récupérer l'objet PDO

        $stmt = $pdo->prepare("INSERT IGNORE INTO stations (num_station, nom, latitude, longitude, altitude, libgeo, codegeo, nom_epci, code_epci, nom_dept, code_dept, nom_reg, code_reg) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Lier les paramètres
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
        
        // Exécuter la requête
        if (!$stmt->execute()) {
            throw new Exception("Erreur lors de l'insertion : " . implode(", ", $stmt->errorInfo()));
        } 
    }
}

?>