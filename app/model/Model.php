<?php
class Model {
    //On stocke l'unique instance de la classe Model dans la variable statique 'instance'
    //Elle est initialisée à null pour indiquer qu'aucune instance n'a été créée
    private static $instance = null;
    private $pdo;
    public function __construct() {
        try {
            $this->pdo = new PDO('mysql:host=localhost;dbname=SAE3.01', 'root', '');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Échec de la connexion à la base de données : " . $e->getMessage());
        }
    }
    public function getPdo() {
        return $this->pdo;
    }
    public static function getInstance() {
        if (is_null(self::$instance)) {
            self::$instance = new Model();
        }
        return self::$instance;
    }
    

}
?>