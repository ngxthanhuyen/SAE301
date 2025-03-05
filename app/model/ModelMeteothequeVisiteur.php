<?php
require_once __DIR__ . '/../model/Model.php';

class ModelMeteothequeVisiteur {
    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }
    // Méthode pour récupérer les météothèques publiées
    public function getMeteothequesPubliees() {
        // Requête SQL pour récupérer les météothèques publiées avec le prénom et nom des utilisateurs
        $sql = "SELECT m.meteotheque_id, u.user_id, u.prenom, u.nom, u.username,  GROUP_CONCAT(num_station SEPARATOR ', ') AS stations
                FROM meteotheque m
                JOIN users u ON m.user_id = u.user_id
                WHERE m.publication = 1
                GROUP BY u.user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Méthode pour récupérer une météothèque par son ID
    public function getMeteothequeById($meteothequeId) {
        // Requête SQL pour récupérer la météothèque spécifique avec prénom et nom
        $sql = "SELECT m.meteotheque_id, u.prenom, u.nom, u.username 
                FROM meteotheque m
                JOIN users u ON m.user_id = u.user_id
                WHERE m.meteotheque_id = :meteotheque_id";

        // Préparation et exécution de la requête
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':meteotheque_id', $meteothequeId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Récupère les stations favorites pour une météothèque
    public function getStationsFavoritesByMeteothequeId($userId) {
        $sql = "SELECT s.nom, s.num_station, s.libgeo, s.codegeo, s.nom_reg, s.code_reg
                FROM stations AS s
                INNER JOIN meteotheque AS m ON m.num_station = s.num_station
                WHERE m.user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Récupère les tableaux de comparaison pour une météothèque
    public function getTableauxComparaisonByMeteothequeId($meteothequeId) {
        $sql = "SELECT * FROM comparaisons c JOIN  meteotheque m ON m.user_id = c.user_id WHERE m.meteotheque_id = :meteotheque_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':meteotheque_id', $meteothequeId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    //Afficher les météothèques qui contiennent la station cherchée par l'utilisateur
    public function rechercherMeteothequesParStation($query) {        
       $sql = "SELECT m.meteotheque_id, m.user_id, m.num_station, s.nom AS nom_station, u.username, u.prenom, u.nom
               FROM meteotheque m
               JOIN users u ON m.user_id = u.user_id
               JOIN stations s ON m.num_station = s.num_station
               WHERE (s.nom LIKE :query OR m.num_station LIKE :query)
               AND m.publication = 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['query' => $query . '%']);
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Récupère les alertes par l'id de l'utilisateur
    public function getAlertesByUserId($userId) {
        $sql = "SELECT * FROM alertes WHERE user_id = :user_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}