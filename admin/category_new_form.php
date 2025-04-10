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
?>

<h2>Ajouter une nouvelle catégorie</h2>

<div class="form-container">
    <form method="POST" action="category_new.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        
        <div class="form-block">
            <label for="name">Nom de la catégorie *</label>
            <input type="text" id="name" name="name" required>
        </div>
        
        <div class="form-block">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>
        </div>
        
        <input type="submit" name="category_new_submit" value="Enregistrer la catégorie">
    </form>
    
    <div class="form-actions">
        <a href="category_index.php" class="btn btn-secondary">Annuler</a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>