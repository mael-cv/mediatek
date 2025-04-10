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

// Générer un token CSRF pour le formulaire
$csrfToken = generateCsrfToken();
?>

<h2>Modifier un utilisateur</h2>

<div class="form-container">
    <h4>Formulaire de modification de l'utilisateur</h4>
    
    <form action="user_edit.php" method="POST" novalidate>
        <!-- Token CSRF caché -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <!-- ID de l'utilisateur caché -->
        <input type="hidden" name="id" value="<?= $userId ?>">
        
        <div class="form-block">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required 
                   value="<?= htmlspecialchars($user['email']) ?>">
        </div>

        <div class="form-block">
            <label for="last_name">Nom</label>
            <input type="text" id="last_name" name="last_name" required 
                   value="<?= htmlspecialchars($user['last_name']) ?>">
        </div>

        <div class="form-block">
            <label for="first_name">Prénom</label>
            <input type="text" id="first_name" name="first_name" required 
                   value="<?= htmlspecialchars($user['first_name']) ?>">
        </div>

        <div class="form-block">
            <label for="address">Adresse</label>
            <textarea id="address" name="address" rows="2"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <div class="form-block">
            <label for="zip_code">Code postal</label>
            <input type="text" id="zip_code" name="zip_code" 
                   value="<?= htmlspecialchars($user['zip_code']) ?>">
        </div>

        <div class="form-block">
            <label for="birth_date">Date de naissance</label>
            <input type="date" id="birth_date" name="birth_date" 
                   value="<?= $user['birth_date'] ?>">
        </div>

        <div class="form-block">
            <label for="is_admin">Administrateur</label>
            <select id="is_admin" name="is_admin">
                <option value="0" <?= $user['is_admin'] ? '' : 'selected' ?>>Non</option>
                <option value="1" <?= $user['is_admin'] ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>

        <div class="form-block">
            <label for="email_verified">Email vérifié</label>
            <select id="email_verified" name="email_verified">
                <option value="0" <?= $user['email_verified'] ? '' : 'selected' ?>>Non</option>
                <option value="1" <?= $user['email_verified'] ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>

        <div class="form-block">
            <label for="account_locked">Compte verrouillé</label>
            <select id="account_locked" name="account_locked">
                <option value="0" <?= $user['account_locked'] ? '' : 'selected' ?>>Non</option>
                <option value="1" <?= $user['account_locked'] ? 'selected' : '' ?>>Oui</option>
            </select>
        </div>
        
        <div class="form-block">
            <label for="new_password">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
            <input type="password" id="new_password" name="new_password">
            <small>8 caractères minimum si modifié</small>
        </div>

        <input type="submit" name="user_edit_submit" value="Enregistrer les modifications">
    </form>
    
    <div class="form-actions">
        <a href="user_show.php?id=<?= $userId ?>" class="btn btn-secondary">Annuler</a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>