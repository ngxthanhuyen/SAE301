<?php
require_once __DIR__ . '/../model/Model.php';
require_once __DIR__ . '/../model/ModelUsers.php';

class ControllerUser {
    public function index() {
        //On vérifie d'abord si l'utilisateur est un visiteur
        if (isset($_GET['visiteur']) && $_GET['visiteur'] == 'true') {
            $_SESSION['is_visiteur'] = true;
        } else {
            // Si un utilisateur est connecté, on retire le statut de visiteur
            unset($_SESSION['is_visiteur']);
        }

        // Détermine le nom d'utilisateur à afficher
        if (isset($_SESSION['is_visiteur']) && $_SESSION['is_visiteur'] === true) {
            return [
                'username' => 'visiteur',
                'role' => 'visiteur',
            ];
        } elseif (isset($_SESSION['username'])) {
            return [
                'id' => $_SESSION['user_id'],
                'nom' => $_SESSION['nom'],
                'prenom' => $_SESSION['prenom'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'creation_date' => $_SESSION['creation_date'],
                'role' => $_SESSION['role'],
            ];
        }
        return null; 
    }

    // Méthode pour récupérer tous les utilisateurs
    public function getAllUsers() {
        $model = new ModelUsers();  
        return $model->getAllUsers();  
    }

    public function updateUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $nom = $_POST['nom'] ?? null;
            $prenom = $_POST['prenom'] ?? null;
            $username = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
    
            if (!$id) {
                echo json_encode(['message' => 'ID utilisateur manquant', 'success' => false]);
                exit;
            }
    
            $model = new ModelUsers();
            $result = $model->updateUser($id, $nom, $prenom, $username, $email);
    
            // Redirection après mise à jour
            if ($result) {
                // Utilisation d'une session pour afficher un message de succès
                $_SESSION['success'] = 'Utilisateur mis à jour avec succès.';
                header("Location: ?page=admin_page");
                exit;
            } else {
                echo json_encode(['message' => 'Erreur lors de la mise à jour', 'success' => false]);
                exit;
            }
        }
    }
    

    public function getUserById($id) {
        $model = new ModelUsers();
        return $model->getUserById($id);
    }
    
    
    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
                exit;
            }
    
            $model = new ModelUsers();
            $result = $model->deleteUser($id);
            
            // Retourne directement le résultat du modèle
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        
        // Si la méthode n'est pas POST
        echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        exit;
    }

    //Méthode pour gérer la mise à jour du profil
    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $nom = $_POST['nom'] ?? null;
            $prenom = $_POST['prenom'] ?? null;
            $username = $_POST['username'] ?? null;
            $email = $_POST['email'] ?? null;
            $photo_profil = $_FILES['photo_profil'] ?? null;

            // Traitement de la photo de profil
            $target_file = '';
            if ($photo_profil && $photo_profil['error'] === 0) {
                $target_dir = __DIR__ . '/../upload/'; 

                // Vérifier l'extension du fichier
                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                $extension = strtolower(pathinfo($photo_profil['name'], PATHINFO_EXTENSION));
                if (!in_array($extension, $allowedExtensions)) {
                    $_SESSION['error'] = "L'extension du fichier doit être jpg, jpeg ou png.";
                    header("Location: ?page=user_page");
                    exit;
                }

                // Vérifier la taille du fichier (max 2 Mo)
                $maxSize = 2 * 1024 * 1024; 
                if ($photo_profil['size'] > $maxSize) {
                    $_SESSION['error'] = "Le fichier ne doit pas dépasser 2 Mo.";
                    header("Location: ?page=user_page");
                    exit;
                }

                $fileName = basename($photo_profil['name']);
                $target_file = $target_dir . $fileName;

                if (file_exists($target_file)) {
                    $fileName = pathinfo($photo_profil['name'], PATHINFO_FILENAME) . '_' . uniqid() . '.' . $extension;
                    $target_file = $target_dir . $fileName;
                }

                //On déplace le fichier vers le répertoire de téléchargement
                if (!move_uploaded_file($photo_profil['tmp_name'], $target_file)) {
                    $_SESSION['error'] = "Erreur lors de l'upload de la photo.";
                    header("Location: ?page=user_page");
                    exit;
                }
            } else {
                //Si aucune photo n'est téléchargée, conserver l'ancienne photo
                $target_file = $_SESSION['photo_profil'] ?? '';
            }

            // Mise à jour du profil dans la base de données
            $model = new ModelUsers();
            $updateSuccess = $model->updateProfile($nom, $prenom, $username, $email, $target_file);

            // Si la mise à jour est réussie
            if ($updateSuccess) {
                // Mise à jour des informations dans la session
                $_SESSION['nom'] = $nom ?: $_SESSION['nom'];
                $_SESSION['prenom'] = $prenom ?: $_SESSION['prenom'];
                $_SESSION['username'] = $username ?: $_SESSION['username'];
                $_SESSION['email'] = $email ?: $_SESSION['email'];
                $_SESSION['photo_profil'] = $target_file ?: $_SESSION['photo_profil'];

                $_SESSION['success'] = "Profil mis à jour avec succès.";
            } else {
                $_SESSION['error'] = "Erreur lors de la mise à jour du profil.";
            }

            // Redirection vers la page de l'utilisateur
            header("Location: ?page=user_page");
            exit;
        }
    }
}
?>
