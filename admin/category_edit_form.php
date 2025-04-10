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
    echo "<div class='error-message'>ID de catégorie invalide.</div>";
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

$categoryId = intval($_GET['id']);

// Récupérer les détails de la catégorie
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM categories WHERE id = ?");
    $stmt->execute([$categoryId]);
    $category = $stmt->fetch();

    if (!$category) {
        echo "<div class='error-message'>Catégorie non trouvée.</div>";
        echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    include_once "./partials/bottom.php";
    exit;
}
?>

<h2>Modifier une catégorie</h2>

<div class="form-container">
    <form method="POST" action="category_edit.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="id" value="<?= $categoryId ?>">
        
        <div class="form-block">
            <label for="name">Nom de la catégorie *</label>
            <input type="text" id="name" name="name" value="<?= htmlspecialchars($category['name']) ?>" required>
        </div>
        
        <div class="form-block">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
        </div>
        
        <input type="submit" name="category_edit_submit" value="Enregistrer les modifications">
    </form>
    
    <div class="form-actions">
        <a href="category_index.php" class="btn btn-secondary">Annuler</a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>