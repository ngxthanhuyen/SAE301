<?php
require_once __DIR__ . '/../model/Model.php';

class ModelComparaisons {
    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }

    public function insertComparison($data) {
        // Vérifier si la comparaison existe déjà
        $queryCheck = "SELECT COUNT(*) FROM comparaisons 
                       WHERE station1 = :station1 
                       AND station2 = :station2 
                       AND date_comp = :date_comp
                       AND heure_comp = :heure_comp";
        
        $stmtCheck = $this->pdo->prepare($queryCheck);
        $stmtCheck->execute([
            ':station1' => $data['station1'],
            ':station2' => $data['station2'],
            ':date_comp' => $data['date_comp'],
            ':heure_comp' => $data['heure_comp']
        ]);
        
        // Si une comparaison existe déjà, on l'insère pas
        if ($stmtCheck->fetchColumn() > 0) {
            return; 
        }
        
        // Si la comparaison n'existe pas, on procède à l'insertion
        $query = "INSERT INTO comparaisons 
                  (user_id, station1, station2, date_comp, heure_comp, temp_s1, temp_s2, temp_ec, hum_s1, hum_s2, hum_ec, 
                   prec_s1, prec_s2, prec_ec, vent_s1, vent_s2, vent_ec, press_s1, press_s2, press_ec, vis_s1, vis_s2, vis_ec) 
                  VALUES 
                  (:user_id, :station1, :station2, :date_comp, :heure_comp, :temp_s1, :temp_s2, :temp_ec, :hum_s1, :hum_s2, :hum_ec, 
                   :prec_s1, :prec_s2, :prec_ec, :vent_s1, :vent_s2, :vent_ec, :press_s1, :press_s2, :press_ec, :vis_s1, :vis_s2, :vis_ec)";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($data);
    }
    
    public function getUserComparisons($userId) {
        $query = "SELECT * FROM comparaisons WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    public function deleteComparison($comparisonId) {
        // Requête pour supprimer une comparaison en fonction de son ID
        $query = "DELETE FROM comparaisons WHERE id = :id";
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute([':id' => $comparisonId]);
    }    
}
?>
