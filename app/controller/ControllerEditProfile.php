<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);
include_once __DIR__ . '/../model/ModelUsers.php'; 

// Vérifier si le formulaire est soumis
if (isset($_POST['update_profile'])) {
    // Récupérer les données du formulaire
    $nom = !empty($_POST['nom']) ? $_POST['nom'] : null;
    $prenom = !empty($_POST['prenom']) ? $_POST['prenom'] : null;
    $username = !empty($_POST['username']) ? $_POST['username'] : null;
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $photo_profil = !empty($_FILES['photo_profil']['name']) ? $_FILES['photo_profil'] : null;    

    // Traitement photo de profil
    if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
        $fileType = mime_content_type($_FILES['photo_profil']['tmp_name']);
        if (strpos($fileType, 'image') === false) {
            $_SESSION['error'] = 'Le fichier téléchargé n\'est pas une image.';
            header('Location: ../view/edit.php');
            exit();
        }

        $uploadDir = __DIR__ . '/../upload/';
        $filePath = $uploadDir . basename($_FILES['photo_profil']['name']);
        
        if (move_uploaded_file($_FILES['photo_profil']['tmp_name'], $filePath)) {
            $_SESSION['photo_profil'] = $filePath;  
            $photo_profil = $filePath; 
        } else {
            $_SESSION['error'] = 'Erreur lors du téléchargement de la photo.';
            header('Location: ../view/edit.php');
            exit();
        }
    }
    $modelUsers = new ModelUsers();
    $email_or_username = $_SESSION['user_id'];

    // Appel à la méthode de mise à jour dans la base de données
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
