<?php
session_start();

class ControllerUser {
    public function index() {
        //On vérifie si l'utilisateur est un visiteur
        if (isset($_GET['visiteur']) && $_GET['visiteur'] == 'true') {
            $_SESSION['is_visiteur'] = true;
        } else {
            //Si un utilisateur est connecté, on retire le statut de visiteur
            unset($_SESSION['is_visiteur']);
        }

        //On détermine le nom d'utilisateur à afficher
        if (isset($_SESSION['is_visiteur']) && $_SESSION['is_visiteur'] === true) {
            return [
                'username' => 'visiteur',
                'role' => 'visiteur',
            ];
        } elseif (isset($_SESSION['username'])) {
            return [
                'nom' => $_SESSION['nom'],
                'prenom' => $_SESSION['prenom'],
                'username' => $_SESSION['username'],
                'email' => $_SESSION['email'],
                'creation_date' => $_SESSION['creation_date'],
                'role' => 'utilisateur',
            ];
            }
        return null; 
    }

    //Méthode pour gérer la mise à jour du profil
    public function updateProfile() {
        if (isset($_SESSION['update_profile']) && $_SERVER['REQUEST_METHOD'] === 'POST') {
            // Récupération des données du formulaire
            $nom = !empty($_POST['nom']) ? $_POST['nom'] : null;
            $prenom = !empty($_POST['prenom']) ? $_POST['prenom'] : null;
            $username = !empty($_POST['username']) ? $_POST['username'] : null;
            $email = !empty($_POST['email']) ? $_POST['email'] : null;
            $photo_profil = !empty($_FILES['photo_profil']['name']) ? $_FILES['photo_profil'] : null;
    
            // Traitement de l'upload de la photo de profil (si une photo est uploadée)
            $target_file = "";
            if ($photo_profil) {
                $target_dir = "../upload/";
                $target_file = $target_dir . basename($photo_profil["name"]);
                if (!move_uploaded_file($photo_profil["tmp_name"], $target_file)) {
                    $_SESSION['error'] = "Erreur lors de l'upload de la photo de profil.";
                    header("Location: ../view/user_page.php");
                    exit;
                }
            } else {
                // Si aucune photo n'est uploadée, on garde l'ancienne image
                $target_file = $_SESSION['photo_profil'] ?? ''; 
            }
    
            $model = new ModelUsers();
            
            // On vérifie si au moins un champ a été rempli, sinon on renvoie une erreur
            if (!$nom && !$prenom && !$username && !$email && !$photo_profil) {
                $_SESSION['error'] = "Veuillez remplir au moins un champ à mettre à jour.";
                header("Location: ../view/user_page.php");
                exit;
            }
    
            try {
                //Mise à jour des informations
                $updateSuccess = $model->updateProfile($nom, $prenom, $username, $email, $target_file);
    
                if ($updateSuccess) {
                    // Mise à jour dans la session (si les données sont changées)
                    if ($nom) $_SESSION['nom'] = $nom;
                    if ($prenom) $_SESSION['prenom'] = $prenom;
                    if ($username) $_SESSION['username'] = $username;
                    if ($email) $_SESSION['email'] = $email;
                    if ($target_file) $_SESSION['photo_profil'] = $target_file;
    
                    $_SESSION['success'] = "Profil mis à jour avec succès.";
                } else {
                    $_SESSION['error'] = "Erreur lors de la mise à jour du profil dans la base de données.";
                }
            } catch (Exception $e) {
                $_SESSION['error'] = "Une exception s'est produite : " . $e->getMessage();
            }
            
            header("Location: ../view/user_page.php");
            exit;
        } else {
            header("Location: ../view/user_page.php");
        }
    }
    
}
?>
