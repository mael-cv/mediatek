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

// Récupérer les catégories depuis la base de données
try {
    $db = getDbConnection();
    
    // Vérifier si la table existe
    $checkTable = $db->query("SHOW TABLES LIKE 'categories'");
    if ($checkTable->rowCount() == 0) {
        // Créer la table si elle n'existe pas
        $db->exec("CREATE TABLE categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )");
        
        // Insérer quelques catégories par défaut
        $db->exec("INSERT INTO categories (name, description) VALUES 
            ('Roman', 'Livres de fiction avec une intrigue narrative'),
            ('Science-Fiction', 'Œuvres spéculatives basées sur des innovations scientifiques imaginaires'),
            ('Biographie', 'Récits détaillant la vie d\'une personne')");
    }
    
    // Récupérer toutes les catégories
    $categories = $db->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
    
    // Compter le nombre de livres par catégorie
    $bookCountQuery = $db->query("SELECT category_id, COUNT(*) as book_count FROM books_categories GROUP BY category_id");
    $bookCounts = [];
    while ($row = $bookCountQuery->fetch()) {
        $bookCounts[$row['category_id']] = $row['book_count'];
    }
    
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Message de succès
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}
?>

<h2>Gestion des catégories</h2>

<div class="action-bar">
    <a href="category_new_form.php" class="btn btn-primary">
        <i class="light-icon-plus"></i> Ajouter une catégorie
    </a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Description</th>
            <th>Livres</th>
            <th>Date de création</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($categories) && count($categories) > 0): ?>
            <?php foreach ($categories as $category): ?>
            <tr>
                <td><?= $category['id'] ?></td>
                <td><?= htmlspecialchars($category['name']) ?></td>
                <td><?= htmlspecialchars($category['description'] ?? '') ?></td>
                <td><?= isset($bookCounts[$category['id']]) ? $bookCounts[$category['id']] : 0 ?></td>
                <td><?= date('d/m/Y', strtotime($category['created_at'])) ?></td>
                <td class="actions">
                    <a href="category_edit_form.php?id=<?= $category['id'] ?>" title="Modifier cette catégorie" class="btn btn-secondary btn-sm me-1">
                        <i role="button" class="light-icon-pencil"></i>
                    </a>
                    <a href="category_delete_form.php?id=<?= $category['id'] ?>" title="Supprimer cette catégorie" class="btn btn-danger btn-sm">
                        <i role="button" class="light-icon-trash"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" class="text-center">Aucune catégorie enregistrée</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
include_once "./partials/bottom.php";
?>