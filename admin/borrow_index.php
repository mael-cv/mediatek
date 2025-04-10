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

// Récupérer les emprunts depuis la base de données
try {
    $db = getDbConnection();
    $query = "SELECT b.id, b.borrow_date, b.return_date, 
              u.id as user_id, u.email, u.first_name, u.last_name,
              bk.id as book_id, bk.title, bk.isbn
              FROM borrows b
              JOIN users u ON b.user_id = u.id
              JOIN books bk ON b.book_id = bk.id
              ORDER BY b.borrow_date DESC";
    $borrows = $db->query($query)->fetchAll();
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
}

// Message de succès
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}
?>

<h2>Gestion des emprunts</h2>

<div class="table-top">
    <div class="table-info">
        <p>Liste de tous les emprunts</p>
    </div>
</div>

<table class="data-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Livre</th>
            <th>Utilisateur</th>
            <th>Date d'emprunt</th>
            <th>Date de retour</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if(isset($borrows) && count($borrows) > 0): ?>
            <?php foreach ($borrows as $borrow): ?>
            <tr>
                <td><?= $borrow['id'] ?></td>
                <td>
                    <a href="book_show.php?id=<?= $borrow['book_id'] ?>">
                        <?= htmlspecialchars($borrow['title']) ?>
                    </a>
                    <small>ISBN: <?= htmlspecialchars($borrow['isbn']) ?></small>
                </td>
                <td>
                    <a href="user_show.php?id=<?= $borrow['user_id'] ?>">
                        <?= htmlspecialchars($borrow['first_name'] . ' ' . $borrow['last_name']) ?>
                    </a>
                    <small><?= htmlspecialchars($borrow['email']) ?></small>
                </td>
                <td><?= date('d/m/Y', strtotime($borrow['borrow_date'])) ?></td>
                <td>
                    <?= $borrow['return_date'] 
                        ? date('d/m/Y', strtotime($borrow['return_date'])) 
                        : '—' ?>
                </td>
                <td>
                    <?php if($borrow['return_date']): ?>
                        <span class="badge success">Retourné</span>
                    <?php else: ?>
                        <span class="badge warning">En cours</span>
                    <?php endif; ?>
                </td>
                <td class="actions">
                    <?php if(!$borrow['return_date']): ?>
                        <form action="borrow_return.php" method="POST" style="display: inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
                            <input type="hidden" name="borrow_id" value="<?= $borrow['id'] ?>">
                            <button type="submit" class="btn btn-success btn-sm" title="Marquer comme retourné">
                                <i class="light-icon-check"></i> Retourner
                            </button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">Aucun emprunt enregistré</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<?php
include_once "./partials/bottom.php";
?>