<?php
include_once 'navbar.php';
include_once __DIR__ . '/../controller/ControllerUser.php';
require_once __DIR__ . '/../model/ModelUsers.php';

// Vérification que seul un administrateur peut accéder à la page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ?page=login_form');
    exit();
}

// Récupération des utilisateurs
$userController = new ControllerUser();
$users = $userController->getAllUsers();  // Appel à la nouvelle méthode pour récupérer les utilisateurs

// Vérification que la récupération des utilisateurs s'est bien passée
if (!is_array($users)) {
    $users = []; // Évite une erreur si la requête ne retourne rien
}


?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>
    <h2 class="mb-4">Tableau de bord Administrateur</h2>
    
    <a href="?page=register_form" class="btn btn-success mb-3">Ajouter un utilisateur</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Pseudo</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user) : ?>
                <?php if (isset($user['user_id'], $user['nom'], $user['prenom'], $user['username'], $user['email'], $user['role'])) : ?>
                    <tr id="user_<?php echo $user['user_id']; ?>">
                        <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                        <td contenteditable="true" data-field="nom"><?php echo htmlspecialchars($user['nom']); ?></td>
                        <td contenteditable="true" data-field="prenom"><?php echo htmlspecialchars($user['prenom']); ?></td>
                        <td contenteditable="true" data-field="username"><?php echo htmlspecialchars($user['username']); ?></td>
                        <td contenteditable="true" data-field="email"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <?php if ($user['role'] !== 'admin') : ?>
                                <a href="?page=edit_user&id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Modifier</a>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="<?php echo $user['user_id']; ?>">Supprimer</button>
                            <?php else : ?>
                                <span class="text-muted">Admin protégé</span>
                            <?php endif; ?>
                        </td>

                    </tr>

                <?php endif; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".save-btn").click(function() {
            let userId = $(this).data("id");
            let row = $("#user_" + userId);
            let updatedData = {
                id: userId,
                nom: row.find("[data-field='nom']").text(),
                prenom: row.find("[data-field='prenom']").text(),
                username: row.find("[data-field='username']").text(),
                email: row.find("[data-field='email']").text()
            };

            $.post("?page=update_user", updatedData, function(response) {
                alert(response.message);
            }, "json");
        });

        $(".delete-btn").click(function() {
            let userId = $(this).data("id");
            if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
                $.ajax({
                    url: "?page=delete_user",
                    type: "POST",
                    data: { id: userId },
                    dataType: "json",
                    success: function(response) {
                        if (response.success) {
                            $("#user_" + userId).remove();
                            alert(response.message);
                        } else {
                            alert("Erreur: " + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("Erreur technique: " + error);
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    });
</script>

</body>
</html>
