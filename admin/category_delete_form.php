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
    
    // Vérifier si des livres sont associés à cette catégorie
    $checkBooksStmt = $db->prepare("SELECT COUNT(*) FROM books_categories WHERE category_id = ?");
    $checkBooksStmt->execute([$categoryId]);
    $booksCount = $checkBooksStmt->fetchColumn();
    
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    include_once "./partials/bottom.php";
    exit;
}
?>

<h2>Supprimer une catégorie</h2>

<div class="alert alert-danger">
    <h3>Êtes-vous sûr de vouloir supprimer cette catégorie ?</h3>
    <p>Nom: <strong><?= htmlspecialchars($category['name']) ?></strong></p>
    <p>Description: <?= htmlspecialchars($category['description'] ?? 'Aucune description') ?></p>
    
    <?php if ($booksCount > 0): ?>
        <div class="warning-message">
            <p><strong>Attention:</strong> Cette catégorie est associée à <?= $booksCount ?> livre(s). La suppression retirera ces associations.</p>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="category_delete.php" style="margin-top: 20px;">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        <input type="hidden" name="id" value="<?= $categoryId ?>">
        <input type="submit" name="category_delete_submit" value="Confirmer la suppression" class="btn btn-danger">
        <a href="category_index.php" class="btn btn-secondary">Annuler</a>
    </form>
</div>

<?php
include_once "./partials/bottom.php";
?>