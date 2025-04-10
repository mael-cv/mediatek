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

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='error-message'>ID d'utilisateur invalide.</div>";
    echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

$userId = intval($_GET['id']);

// Récupérer les détails de l'utilisateur
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<div class='error-message'>Utilisateur non trouvé.</div>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    include_once "./partials/bottom.php";
    exit;
}
?>

<h2>Détails de l'utilisateur</h2>

<div class="action-bar">
    <a href="user_index.php" class="btn btn-secondary">
        <i class="light-icon-arrow-left"></i> Retour à la liste des utilisateurs
    </a>
    <a href="user_edit_form.php?id=<?= $user['id'] ?>" class="btn btn-primary">
        <i class="light-icon-pencil"></i> Modifier cet utilisateur
    </a>
</div>

<div class="user-details">
    <div class="detail-section">
        <h3>Informations personnelles</h3>
        <p><strong>ID:</strong> <?= $user['id'] ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
        <p><strong>Nom:</strong> <?= htmlspecialchars($user['last_name']) ?></p>
        <p><strong>Prénom:</strong> <?= htmlspecialchars($user['first_name']) ?></p>
        <p><strong>Adresse:</strong> <?= $user['address'] ? htmlspecialchars($user['address']) : '<em>Non renseignée</em>' ?></p>
        <p><strong>Code postal:</strong> <?= $user['zip_code'] ? htmlspecialchars($user['zip_code']) : '<em>Non renseigné</em>' ?></p>
        <p><strong>Date de naissance:</strong> <?= $user['birth_date'] ? date('d/m/Y', strtotime($user['birth_date'])) : '<em>Non renseignée</em>' ?></p>
    </div>

    <div class="detail-section">
        <h3>Statut du compte</h3>
        <p><strong>Administrateur:</strong> <?= $user['is_admin'] ? 'Oui' : 'Non' ?></p>
        <p><strong>Email vérifié:</strong> <?= $user['email_verified'] ? 'Oui' : 'Non' ?></p>
        <p><strong>Compte verrouillé:</strong> <?= $user['account_locked'] ? 'Oui' : 'Non' ?></p>
        <p><strong>Tentatives de connexion échouées:</strong> <?= $user['failed_login_attempts'] ?></p>
        <p><strong>Dernière tentative de connexion:</strong> <?= $user['last_login_attempt'] ? date('d/m/Y H:i:s', strtotime($user['last_login_attempt'])) : '<em>Aucune</em>' ?></p>
    </div>
    
    <div class="detail-section">
        <h3>Dates</h3>
        <p><strong>Créé le:</strong> <?= date('d/m/Y H:i:s', strtotime($user['created_at'])) ?></p>
        <p><strong>Dernière modification:</strong> <?= date('d/m/Y H:i:s', strtotime($user['updated_at'])) ?></p>
    </div>
    
    <div class="detail-section">
        <h3>Actions</h3>
        <?php if ($user['account_locked']): ?>
            <form action="user_unlock.php" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button type="submit" class="btn btn-success">Déverrouiller le compte</button>
            </form>
        <?php endif; ?>
        
        <?php if (!$user['email_verified']): ?>
            <form action="user_verify_email.php" method="POST" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                <button type="submit" class="btn btn-warning">Marquer l'email comme vérifié</button>
            </form>
        <?php endif; ?>
        
        <a href="user_delete_form.php?id=<?= $user['id'] ?>" class="btn btn-danger">
            Supprimer cet utilisateur
        </a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>