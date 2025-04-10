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

// Générer un token CSRF pour le formulaire
$csrfToken = generateCsrfToken();
?>

<h2>Ajouter un nouvel utilisateur</h2>

<div class="form-container">
    <h4>Formulaire de création d'un utilisateur</h4>
    
    <form action="user_new.php" method="POST" novalidate>
        <!-- Token CSRF caché -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
        
        <div class="form-block">
            <label for="email">Email*</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-block">
            <label for="password">Mot de passe*</label>
            <input type="password" id="password" name="password" required>
            <small>8 caractères minimum</small>
        </div>

        <div class="form-block">
            <label for="last_name">Nom*</label>
            <input type="text" id="last_name" name="last_name" required>
        </div>

        <div class="form-block">
            <label for="first_name">Prénom*</label>
            <input type="text" id="first_name" name="first_name" required>
        </div>

        <div class="form-block">
            <label for="address">Adresse</label>
            <textarea id="address" name="address" rows="2"></textarea>
        </div>

        <div class="form-block">
            <label for="zip_code">Code postal</label>
            <input type="text" id="zip_code" name="zip_code">
        </div>

        <div class="form-block">
            <label for="birth_date">Date de naissance</label>
            <input type="date" id="birth_date" name="birth_date">
        </div>

        <div class="form-block">
            <label for="is_admin">Administrateur</label>
            <select id="is_admin" name="is_admin">
                <option value="0" selected>Non</option>
                <option value="1">Oui</option>
            </select>
        </div>

        <div class="form-block">
            <label for="email_verified">Email vérifié</label>
            <select id="email_verified" name="email_verified">
                <option value="0">Non</option>
                <option value="1" selected>Oui</option>
            </select>
        </div>

        <input type="submit" name="user_new_submit" value="Enregistrer le nouvel utilisateur">
    </form>
    
    <div class="form-actions">
        <a href="user_index.php" class="btn btn-secondary">Annuler</a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>