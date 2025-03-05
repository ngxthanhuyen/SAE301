<?php 
session_start();
include_once __DIR__ . '/../model/ModelUsers.php';

// Vérifier si le formulaire est soumis
if (isset($_POST['update_profile'])) {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'] ?? null;
    $prenom = $_POST['prenom'] ?? null;
    $username = $_POST['username'] ?? null;
    $email = $_POST['email'] ?? null;

    // Initialiser la photo de profil par défaut
    $photo_profil = 'avatar.jpg';

    // Vérifier si l'utilisateur veut supprimer la photo actuelle
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        // Supprimer la photo actuelle (si ce n'est pas déjà la photo par défaut)
        if (isset($_SESSION['photo_profil']) && $_SESSION['photo_profil'] != 'avatar.jpg') {
            $photo_path = __DIR__ . '/../upload/' . $_SESSION['photo_profil'];
            if (file_exists($photo_path)) {
                unlink($photo_path); 
            }
        }
        $_SESSION['photo_profil'] = $photo_profil; 
    } else {
        // Traitement photo de profil si un fichier est téléchargé
        if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            $fileName = $_FILES['photo_profil']['name'];
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION)); 
            $maxSize = 2 * 1024 * 1024; 
            $uploadDir = __DIR__ . '/../upload/';
            $filePath = $uploadDir . $fileName;        

            // Vérifier l'extension et la taille
            if (in_array($extension, $allowedExtensions) && $_FILES['photo_profil']['size'] <= $maxSize) {
                if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $filePath)) {
                    // Utilisation de basename() pour obtenir seulement le nom du fichier
                    $_SESSION['photo_profil'] = basename($filePath);  
                    $photo_profil = basename($filePath);
                } else {
                    $_SESSION['error'] = 'Erreur lors du téléchargement de la photo.';
                    header('Location: ../view/edit.php');
                    exit();
                }
            } else {
                $_SESSION['error'] = 'Le fichier doit être une image valide (jpg, jpeg, png) et ne doit pas dépasser 2 Mo.';
                header('Location: ../view/edit.php');
                exit();
            }
        } else {
            // Si aucune photo n'est téléchargée, on conserve l'ancienne photo
            $photo_profil = $_SESSION['photo_profil'] ?? 'avatar_default.png';
        }
    }

    // Appel à la méthode de mise à jour dans la base de données
    $modelUsers = new ModelUsers();
    $updateSuccess = $modelUsers->updateProfile($nom, $prenom, $username, $email, $photo_profil);

    if ($updateSuccess) {
        $_SESSION['success'] = 'Votre profil a été mis à jour avec succès.';
    } else {
        $_SESSION['error'] = 'Erreur lors de la mise à jour du profil.';
    }
    
    header('Location: ../view/edit.php');
    exit();
}
?>