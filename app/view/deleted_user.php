<?php
session_start();
require_once 'navbar.php';

if (!isset($_GET['id'])) {
    echo "<p>Erreur : Aucun utilisateur spécifié.</p>";
    exit;
}

$id = htmlspecialchars($_GET['id']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suppression de l'utilisateur</title>
    <link rel="stylesheet" href="/SAE301/static/style/edit.css"/>
</head>
<body>

<div class="container">
    <div class="form-card">
        <h2>Utilisateur supprimé</h2>
        <p>L'utilisateur avec l'ID <strong><?= $id ?></strong> a été supprimé avec succès.</p>
        <p><a href="?page=users_list">Retour à la liste des utilisateurs</a></p>
    </div>
</div>

</body>
</html>
