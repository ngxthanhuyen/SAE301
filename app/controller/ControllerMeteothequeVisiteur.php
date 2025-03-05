<?php
require_once __DIR__ . '/../model/ModelMeteothequeVisiteur.php';

class ControllerMeteothequeVisiteur {
    private $model;

    public function __construct() {
        $this->model = new ModelMeteothequeVisiteur();
    }
    // Récupère toutes les météothéques publiées par les utilisateurs
    public function afficherMeteothequesPubliees() {
       return $this->model->getMeteothequesPubliees();
    }
    //Récuprérer la météothèque 
    public function getMeteothequeById($meteothequeId) {
        return $this->model->getMeteothequeById($meteothequeId);
    }
    // Affiche les détails d'une météothèque
    public function afficherDetailsMeteotheque($userId) {
        return $this->model->getStationsFavoritesByMeteothequeId($userId);
    }
    public function getTableauxComparaisonByMeteothequeId($meteothequeId) {
        return $this->model->getTableauxComparaisonByMeteothequeId($meteothequeId);
    }
    public function rechercherMeteothequesParStation($query) {
        return $this->model->rechercherMeteothequesParStation($query);
    }    
    public function getAlertesSauvegardeesParUtilisateur($userId) {
        return $this->model->getAlertesByUserId($userId);
    }
}




