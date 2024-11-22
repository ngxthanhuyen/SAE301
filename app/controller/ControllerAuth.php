<?php
session_start();
require_once __DIR__ . '/../model/Model.php';
require_once __DIR__ . '/../model/ModelUsers.php';


class ControllerAuth {
    private $user;

    public function __construct() {
        //On obtient l'instance de la base de données depuis la classe Model
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
                if ($row) {
                    if (password_verify($password, $row['password'])) {
                        $_SESSION['user_id'] = $row['user_id'];
                        $_SESSION['nom'] = $row['nom'];
                        $_SESSION['prenom'] = $row['prenom'];
                        $_SESSION['fullname'] = $row['prenom'] . ' ' . $row['nom'];
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['email'] = $row['email'];       
                        $_SESSION['creation_date'] = $row['creation_date']; 
                        $_SESSION['photo_profil'] = $row['photo_profil'];
                        require '../view/user_page.php';  
                        exit();
                    } else {
                        $error[] = "Identifiant ou mot de passe invalide!";
                    }
                } else {
                    $error[] = "Identifiant ou mot de passe invalide!";
                }
            }
        }
        return $error;
    }

    public function register() {
        $error = [];

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register'])) {
            if (empty($_POST['username']) || empty($_POST['nom']) || empty($_POST['prenom']) || empty($_POST['email']) || empty($_POST['password']) || empty($_POST['cpassword'])) {
                $error[] = 'Veuillez remplir tous les champs!';
            } else {
                $username = trim($_POST['username']);
                $nom = trim($_POST['nom']);
                $prenom = trim($_POST['prenom']);
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $cpassword = $_POST['cpassword'];
                $photo_profil = null;  

                // Dossier pour les images
                $uploadDir = __DIR__ . '/../upload/';
                echo "Vérification de l'existence du dossier...<br>";
                if (!is_dir($uploadDir)) {
                    echo 'Le répertoire n\'existe pas, création en cours...<br>';
                    if (!mkdir($uploadDir, 0777, true)) {
                        $error[] = 'Impossible de créer le répertoire pour l\'upload.';
                    }
                } else {
                    echo 'Répertoire trouvé : ' . realpath($uploadDir) . '<br>';
                }

                // Vérification du fichier uploadé
                if (isset($_FILES['photo_profil']) && $_FILES['photo_profil']['error'] == 0) {
                    echo 'Fichier téléchargé : ' . $_FILES['photo_profil']['name'] . '<br>';
                    echo 'Type de fichier : ' . $_FILES['photo_profil']['type'] . '<br>';
                    echo 'Taille du fichier : ' . $_FILES['photo_profil']['size'] . ' octets<br>';

                    $allowed_types = ['image/jpeg', 'image/png'];
                    if (in_array($_FILES['photo_profil']['type'], $allowed_types)) {
                        // Vérification de la taille du fichier
                        $maxFileSize = 2 * 1024 * 1024; // 2 Mo
                        if ($_FILES['photo_profil']['size'] > $maxFileSize) {
                            $error[] = 'Le fichier est trop volumineux. Veuillez choisir un fichier de moins de 2 Mo.';
                        } else {
                            $photo_profil = $uploadDir . uniqid() . '_' . basename($_FILES['photo_profil']['name']);
                            
                            // Déplacer le fichier
                            if (!move_uploaded_file($_FILES['photo_profil']['tmp_name'], $photo_profil)) {
                                $error[] = 'Erreur lors du téléchargement de la photo de profil.';
                                echo 'Erreur lors du déplacement du fichier : ' . $_FILES['photo_profil']['tmp_name'] . ' vers ' . $photo_profil . '<br>';
                            } else {
                                echo 'Photo téléchargée avec succès !<br>';
                            }
                        }
                    } else {
                        $error[] = 'Le fichier téléchargé n\'est pas une image valide.';
                    }
                } else {
                    $error[] = 'Erreur lors du téléchargement de la photo de profil. Code erreur: ' . $_FILES['photo_profil']['error'];
                    echo 'Erreur de téléchargement : ' . $_FILES['photo_profil']['error'] . '<br>'; // Affiche l'erreur
                }

                // Vérification de l'existence de l'utilisateur et des mots de passe
                if ($this->user->exists($username, $email)) {
                    $error[] = 'Cet utilisateur existe déjà!';
                } elseif ($password !== $cpassword) {
                    $error[] = 'Les mots de passe ne correspondent pas!';
                } else {
                    // Création de l'utilisateur
                    if ($this->user->create($username, $nom, $prenom, $email, $password, $photo_profil)) {
                        echo 'Utilisateur créé avec succès.<br>';
                        header('Location: ../view/login_form.php');  // Rediriger vers la page de connexion
                        exit();
                    } else {
                        $error[] = 'Erreur lors de la création de l\'utilisateur.';
                    }
                }
            }
        }
        return $error;
    }
}
?>
