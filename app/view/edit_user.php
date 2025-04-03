<?php
require_once __DIR__ . '/../controller/ControllerUser.php';

$userId = $_GET['id'] ?? null; // Récupérer l'ID de l'utilisateur depuis l'URL

if ($userId) {
    $controller = new ControllerUser();
    $userData = $controller->getUserById($userId);
} else {
    $userData = null; // Gérer le cas où aucun ID n'est fourni
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Édition de l'utilisateur</title>
    <link rel="stylesheet" href="/SAE301/static/style/edit.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<div class="container">
    <div class="form-card">
        <h2>Modifier l'utilisateur</h2>


        <form action="?page=update_user" method="POST">
            <input type="hidden" name="id" value="<?= htmlspecialchars($userData['user_id']) ?>">
            <label for="nom">Nom :</label>
            <input type="text" name="nom" value="<?= htmlspecialchars($userData['nom'] ?? '', ENT_QUOTES) ?>"><br><br>
            <label for="prenom">Prénom :</label>
            <input type="text" name="prenom" value="<?= htmlspecialchars($userData['prenom'] ?? '', ENT_QUOTES) ?>"><br><br>
            <label for="username">Pseudo :</label>
            <input type="text" name="username" value="<?= htmlspecialchars($userData['username'] ?? '', ENT_QUOTES) ?>"><br><br>
            <label for="email">Email :</label>
            <input type="text" name="email" value="<?= htmlspecialchars($userData['email'] ?? '', ENT_QUOTES) ?>"><br><br>
            <button type="submit">Mettre à jour</button>
        </form>


        <p><a href="?page=admin_page">Retour à la liste des utilisateurs</a></p>
    </div>
</div>

</body>
</html>
