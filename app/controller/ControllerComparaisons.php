<?php
require_once __DIR__ . '/../model/ModelComparaisons.php';

class ControllerComparaisons {
    private $model;

    public function __construct() {
        $this->model = new ModelComparaisons();
    }

    public function insertComparison($data) {
        try {
            // Insérer les données dans la table comparaisons
            $this->model->insertComparison($data);
            
            // Définir un message flash de succès avec type 'success'
            $_SESSION['flash_message'] = [
                'message' => 'Le tableau de comparaison a été ajouté avec succès !',
                'type' => 'success' 
            ];
        } catch (Exception $e) {
            // Définir un message flash d'erreur avec type 'error'
            $_SESSION['flash_message'] = [
                'message' => 'Erreur lors de l\'ajout du tableau de comparaison.',
                'type' => 'error' 
            ];
        }
    }
    public function getUserComparisons($userId) {
        return $this->model->getUserComparisons($userId);
    }
      
    public function deleteComparison($comparisonId) {
        // Appeler la méthode deleteComparison du modèle pour supprimer la comparaison
        $success = $this->model->deleteComparison($comparisonId);
        
        // Message flash après suppression
        if ($success) {
            $_SESSION['flash'] = [
                'message' => 'Ce tableau de comparaison a été supprimé de votre météothèque avec succès!',
                'type' => 'success'
            ];
        } else {
            $_SESSION['flash'] = [
                'message' => 'Erreur lors de la suppression du tableau de comparaison.',
                'type' => 'error'
            ];
        }
        
        // Retourner une réponse au contrôleur (message et statut)
        return ['success' => $success, 'message' => $success ? 'Tableau de comparaison supprimé de la météothèque' : 'Erreur lors de la suppression'];
    }    
}
?>
