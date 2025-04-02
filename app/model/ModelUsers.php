<?php
include_once __DIR__ . '/../model/Model.php';

class ModelUsers {
    private $pdo;

    public function __construct() {
        $this->pdo = Model::getInstance()->getPdo();
        $this->checkAndAddRoleColumn();
    }

    public function findUser($email_or_username) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = :email_or_username OR username = :email_or_username");
        $stmt->execute(['email_or_username' => $email_or_username]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($username, $nom, $prenom, $email, $password, $photo_profil) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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
            error_log("Erreur lors de l'insertion de l'utilisateur : " . $e->getMessage());
            return false;
        }
    }

    public function updateUser($id, $nom, $prenom, $username, $email) {
        $stmt = $this->pdo->prepare("UPDATE users SET nom = ?, prenom = ?, username = ?, email = ? WHERE user_id = ?");
        return $stmt->execute([$nom, $prenom, $username, $email, $id]);
    }
    
    public function deleteUser($id) {
        try {
            // Désactiver temporairement les contraintes de clé étrangère
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
            
            // Supprimer d'abord les enregistrements liés dans les autres tables
            $tablesLiees = ['alertes', 'comparaisons', 'meteotheque'];
            foreach ($tablesLiees as $table) {
                $stmt = $this->pdo->prepare("DELETE FROM $table WHERE user_id = ?");
                $stmt->execute([$id]);
            }
            
            // Ensuite supprimer l'utilisateur
            $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = ?");
            $success = $stmt->execute([$id]);
            
            // Réactiver les contraintes
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            
            return [
                'success' => $success, 
                'message' => $success ? 'Utilisateur et données associées supprimés' : 'Aucun utilisateur trouvé avec cet ID'
            ];
        } catch (PDOException $e) {
            // S'assurer que les contraintes sont réactivées même en cas d'erreur
            $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
            error_log("Erreur suppression utilisateur : " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Erreur base de données : ' . $e->getMessage()
            ];
        }
    }
    

    public function exists($username, $email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
        $stmt->execute(['username' => $username, 'email' => $email]);
        return $stmt->rowCount() > 0;
    }

    public function checkAndAddRoleColumn() {
        try {
            // Vérifier si la colonne existe déjà
            $stmt = $this->pdo->prepare("SHOW COLUMNS FROM users LIKE 'role'");
            $stmt->execute();
            $columnExists = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$columnExists) {
                // Ajouter la colonne si elle n'existe pas
                $sql = "ALTER TABLE users 
                        ADD COLUMN role ENUM('user', 'admin') 
                        CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci 
                        DEFAULT 'user' 
                        AFTER photo_profil"; // ou à la position que vous souhaitez
                
                $this->pdo->exec($sql);
                error_log("Colonne 'role' ajoutée à la table users");
                return true;
            }
            
            return false; // La colonne existait déjà
        } catch (PDOException $e) {
            error_log("Erreur lors de l'ajout de la colonne role: " . $e->getMessage());
            return false;
        }
    }

    public function createAdminIfNotExists() {
        $this->checkAndAddRoleColumn();
        $adminEmail = "admin@admin.com"; 
        $adminUsername = "admin";
        $adminPassword = "wiwi49"; // Mot de passe par défaut pour l'admin
    
        // Vérifie si l'admin existe déjà
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = 'admin'");
        $stmt->execute();
        
        if (!$stmt->fetch()) { 
            $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
            $stmt = $this->pdo->prepare("INSERT INTO users (username, nom, prenom, email, password, role) 
                                        VALUES (:username, 'Admin', 'Super', :email, :password, 'admin')");
            $stmt->execute([
                'username' => $adminUsername,
                'email' => $adminEmail,
                'password' => $hashedPassword
            ]);
        }
    }

    public function getUserById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE user_id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    

    public function getAllUsers() {
        $stmt = $this->pdo->prepare("SELECT * FROM users");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    

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
