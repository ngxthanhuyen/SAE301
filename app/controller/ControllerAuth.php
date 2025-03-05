<?php
session_start();
require_once __DIR__ . '/../model/Model.php';
require_once __DIR__ . '/../model/ModelUsers.php';

class ControllerAuth {
    private $user;

    public function __construct() {
        $pdo = Model::getInstance()->getPdo();
        $this->user = new ModelUsers($pdo);
    }

    public function login() {
        $error = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
            $email_or_username = trim($_POST['email_or_username']);
            $password = trim($_POST['password']);

            if (empty($email_or_username) || empty($password)) {
                $error[] = 'Veuillez remplir tous les champs!';
            } else {
                $row = $this->user->findUser($email_or_username);
                if ($row && password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['nom'] = $row['nom'];
                    $_SESSION['prenom'] = $row['prenom'];
                    $_SESSION['fullname'] = $row['prenom'] . ' ' . $row['nom'];
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['email'] = $row['email'];
                    $_SESSION['creation_date'] = $row['creation_date'];
                    $_SESSION['photo_profil'] = $row['photo_profil'];
                    header('Location: ../view/user_page.php');
                    exit();
                } else {
                $error[] = "Identifiant ou mot de passe invalide!";
                }
            }
        }
        return $error;
    }

    public function register() {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
            $username = trim($_POST['username']);
            $nom = trim($_POST['nom']);
            $prenom = trim($_POST['prenom']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $cpassword = $_POST['cpassword'];
            $photo_profil = null;

            // Validation des champs
            if (empty($username) || empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($cpassword)) {
                $errors[] = 'Veuillez remplir tous les champs.';
            }

            if ($password !== $cpassword) {
                $errors[] = 'Les mots de passe ne correspondent pas.';
            }

            // Gestion de la photo de profil
            $uploadDir = __DIR__ . '/../upload/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); 
            }

            $defaultPhoto = 'avatar.jpg'; 

            if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] === 0) {
                $tmpName = $_FILES['photo_profil']['tmp_name'];
                $name = $_FILES['photo_profil']['name'];
                $size = $_FILES['photo_profil']['size'];

                $allowedExtensions = ['jpg', 'jpeg', 'png'];
                $maxSize = 2 * 1024 * 1024; 
                $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                if (in_array($extension, $allowedExtensions) && $size <= $maxSize) {
                    $fileName = $_FILES['photo_profil']['name'];
                    $filePath = $uploadDir . $fileName;
                    if (move_uploaded_file($tmpName, $filePath)) {
                        $photo_profil = $fileName;
                    } else {
                        $errors[] = 'Erreur lors du téléchargement de la photo. Code d\'erreur : ' . $_FILES['photo_profil']['error'];
                    }
                } else {
                    $errors[] = 'Le fichier doit être une image valide (jpg, jpeg, png) et ne doit pas dépasser 2 Mo.';
                }
            } else {
                $photo_profil = $defaultPhoto;
            }

            // Vérification de l'existence de l'utilisateur
            if ($this->user->exists($username, $email)) {
                $errors[] = 'Un utilisateur avec ce pseudo ou cet email existe déjà.';
            }

            // Création de l'utilisateur
            if (empty($errors)) {
                if ($this->user->create($username, $nom, $prenom, $email, $password, $photo_profil)) {
                    header('Location: ../view/login_form.php');
                    exit();
                } else {
                    $errors[] = 'Erreur lors de la création de l\'utilisateur.';
                }
            }
        }

        return $errors;
    }
}
?>
