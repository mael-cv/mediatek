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

// Récupérer les livres depuis la base de données
$db = getDbConnection();
$query = "SELECT b.*, 
          CASE WHEN br.id IS NOT NULL AND br.return_date IS NULL 
               THEN DATE_FORMAT(br.borrow_date, '%d/%m/%Y') 
               ELSE 'Disponible' 
          END as borrow_status
          FROM books b
          LEFT JOIN (
              SELECT book_id, id, borrow_date, return_date 
              FROM borrows 
              WHERE return_date IS NULL
          ) br ON b.id = br.book_id
          ORDER BY b.id ASC";
$books = $db->query($query)->fetchAll();

// Message de succès
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}
?>

<h2>Gestion des livres</h2>

<div class="action-bar">
    <a href="book_new_form.php" class="btn btn-primary">
        <i class="light-icon-plus"></i> Ajouter un livre
    </a>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Titre</th>
            <th>ISBN</th>
            <th>Résumé</th>
            <th>Année</th>
            <th>Couverture</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($books as $book): ?>
        <tr>
            <td><?= $book['id'] ?></td>
            <td><?= htmlspecialchars($book['title']) ?></td>
            <td><?= htmlspecialchars($book['isbn']) ?></td>
            <td><?= htmlspecialchars($book['summary'] ? (strlen($book['summary']) > 100 ? substr($book['summary'], 0, 100) . '...' : $book['summary']) : '') ?></td>
            <td><?= htmlspecialchars($book['publication_year']) ?></td>
            <td>
                <?php if ($book['cover_path']): ?>
                    <img src="<?= htmlspecialchars('../' . $book['cover_path']) ?>" alt="Couverture" style="max-width: 80px; max-height: 100px;">
                <?php else: ?>
                    <span>Aucune couverture</span>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($book['borrow_status']) ?></td>
            <td>
                <a href="book_show.php?id=<?= $book['id'] ?>" title="Voir le détail de ce livre">
                    <i role="button" class="light-icon-float-left"></i>
                </a>
                <a href="book_edit_form.php?id=<?= $book['id'] ?>" title="Modifier ce livre" class="btn btn-secondary btn-sm me-1">
                    <i role="button" class="light-icon-pencil"></i>
                </a>
                <a href="book_delete_form.php?id=<?= $book['id'] ?>" title="Supprimer ce livre" class="btn btn-danger btn-sm">
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