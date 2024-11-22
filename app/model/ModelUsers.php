<?php

include_once __DIR__ . '/../model/Model.php';
//Cette classe permet de gérer les opérations relatives aux utilisateurs dans la base de données
class ModelUsers {
    //L'attribut qui stocke la connexion PDO à la base de données
    private $pdo;
    //On initialise l'attribut $pdo avec la connexion à la base de données
    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
    }

    //Rechercher un utilisateur dans la base de données en fonction de son email ou son nom d'utilisateur
    public function findUser($email_or_username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email_or_username OR username = :email_or_username");
        $stmt->execute(['email_or_username' => $email_or_username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    //Création d'un nouvel utilisateur dans la base de données avec les informations fournies
    public function create($username, $nom, $prenom, $email, $password, $photo_profil) {
        // Hachage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        // Ajout de l'utilisateur dans la base de données
        try {
            $stmt = $this->pdo->prepare("INSERT INTO users (username, nom, prenom, email, password, photo_profil) VALUES (:username, :nom, :prenom, :email, :password, :photo_profil)");
            $stmt->execute([
                'username' => $username,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'password' => $hashed_password,
                'photo_profil' => $photo_profil 
            ]);
            return true;
        } catch (PDOException $e) {
            // Si une erreur se produit lors de l'insertion
            error_log("Erreur lors de l'insertion de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }
    
    //On vérifie si un utilisateur existe déjà dans la base de données avec le même nom d'utilisateur ou le même mail
    public function exists($username, $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        return $stmt->rowCount() > 0;
    }
    //Méthode pour mettre à jour le profil utilisateur
    public function updateProfile($nom, $prenom, $username, $email, $photo_profil) {
        try {
            $sql = "UPDATE users SET ";
            $params = [];
            
            if (!empty($nom)) {
                $sql .= "nom = :nom, ";
                $params['nom'] = $nom;
                $_SESSION['nom'] = $nom;
            }
            if (!empty($prenom)) {
                $sql .= "prenom = :prenom, ";
                $params['prenom'] = $prenom;
                $_SESSION['prenom'] = $prenom;
            }
            if (!empty($username)) {
                $sql .= "username = :username, ";
                $params['username'] = $username;
                $_SESSION['username'] = $username;
            }
            if (!empty($email)) {
                $sql .= "email = :email, ";
                $params['email'] = $email;
                $_SESSION['email'] = $email;
            }
            if (!empty($photo_profil)) {
                $sql .= "photo_profil = :photo_profil, ";
                $params['photo_profil'] = $photo_profil;
                $_SESSION['photo_profil'] = $photo_profil;
            }
    
            // Supprimer la virgule finale
            $sql = rtrim($sql, ", ") . " WHERE user_id = :user_id";
            $params['user_id'] = $_SESSION['user_id'];
    
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Erreur mise à jour profil : " . $e->getMessage());
            return false;
        }
    }    
}    
?>
