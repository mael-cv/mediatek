<?php
include_once "../utils/config.php";
include_once "./partials/top.php";
include_once "../utils/auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est un admin
if (!isAdmin()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Accès restreint. Veuillez vous connecter avec un compte administrateur.";
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer les utilisateurs depuis la base de données
$db = getDbConnection();
$query = "SELECT id, email, last_name, first_name, is_admin, account_locked, email_verified, created_at
          FROM users
          ORDER BY id ASC";
$users = $db->query($query)->fetchAll();
?>

<h2>Gestion des utilisateurs</h2>

<div class="action-bar">
    <a href="user_new_form.php" class="btn btn-primary">
        <i class="light-icon-plus"></i> Ajouter un utilisateur
    </a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Email</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Administrateur</th>
            <th>Compte vérifié</th>
            <th>Statut du compte</th>
            <th>Date création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td><?= htmlspecialchars($user['last_name']) ?></td>
            <td><?= htmlspecialchars($user['first_name']) ?></td>
            <td><?= $user['is_admin'] ? 'Oui' : 'Non' ?></td>
            <td><?= $user['email_verified'] ? 'Oui' : 'Non' ?></td>
            <td><?= $user['account_locked'] ? '<span class="text-danger">Verrouillé</span>' : 'Actif' ?></td>
            <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
            <td class="actions">
                <a href="user_show.php?id=<?= $user['id'] ?>" title="Voir les détails" class="btn btn-info btn-sm me-1">
                    <i role="button" class="light-icon-eye"></i>
                </a>
                <a href="user_edit_form.php?id=<?= $user['id'] ?>" title="Modifier cet utilisateur" class="btn btn-secondary btn-sm me-1">
                    <i role="button" class="light-icon-pencil"></i>
                </a>
                <a href="user_delete_form.php?id=<?= $user['id'] ?>" title="Supprimer cet utilisateur" class="btn btn-danger btn-sm">
                    <i role="button" class="light-icon-trash"></i>
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php
include_once "./partials/bottom.php";
?>