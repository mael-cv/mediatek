<?php
include_once "../utils/config.php";
include_once "../utils/auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isAuthenticated()) {
    $_SESSION['error_message'] = "Vous devez être connecté pour accéder à cette page.";
    header('Location: ../auth/login.php');
    exit;
}

// Vérifier si l'utilisateur est admin (les admins doivent utiliser le tableau de bord admin)
if (isAdmin()) {
    header('Location: ../admin/index.php');
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db = getDbConnection();

    // Récupérer les emprunts en cours de l'utilisateur
    $borrowsStmt = $db->prepare("
        SELECT b.id, b.borrow_date, bk.id as book_id, bk.title, bk.isbn, bk.cover_path
        FROM borrows b
        JOIN books bk ON b.book_id = bk.id
        WHERE b.user_id = ? AND b.return_date IS NULL
        ORDER BY b.borrow_date DESC
    ");
    $borrowsStmt->execute([$userId]);
    $borrows = $borrowsStmt->fetchAll();
    
    // Récupérer l'historique des emprunts
    $historyStmt = $db->prepare("
        SELECT b.id, b.borrow_date, b.return_date, bk.id as book_id, bk.title, bk.isbn, bk.cover_path
        FROM borrows b
        JOIN books bk ON b.book_id = bk.id
        WHERE b.user_id = ? AND b.return_date IS NOT NULL
        ORDER BY b.return_date DESC
        LIMIT 10
    ");
    $historyStmt->execute([$userId]);
    $history = $historyStmt->fetchAll();

} catch (PDOException $e) {
    error_log('Erreur dans dashboard.php: ' . $e->getMessage());
    $error = "Une erreur est survenue lors de la récupération de vos données.";
}

include_once "../partials/top.php";
?>

<h2>Mon espace utilisateur</h2>

<?php if (isset($error)): ?>
    <div class="error-message"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="dashboard-container">
    <div class="borrowings-section">
        <h3>Mes emprunts en cours</h3>
        <?php if (count($borrows) > 0): ?>
            <div class="book-grid">
                <?php foreach ($borrows as $borrow): ?>
                    <div class="book-card">
                        <div class="book-cover">
                            <?php if ($borrow['cover_path']): ?>
                                <img src="<?= htmlspecialchars('../' . $borrow['cover_path']) ?>" alt="Couverture">
                            <?php else: ?>
                                <div class="no-cover">Aucune couverture</div>
                            <?php endif; ?>
                        </div>
                        <div class="book-info">
                            <h4><?= htmlspecialchars($borrow['title']) ?></h4>
                            <p>ISBN: <?= htmlspecialchars($borrow['isbn']) ?></p>
                            <p>Emprunté le: <?= date('d/m/Y', strtotime($borrow['borrow_date'])) ?></p>
                            <a href="../book_detail.php?id=<?= $borrow['book_id'] ?>" class="btn btn-sm">Voir le livre</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p>Vous n'avez aucun emprunt en cours.</p>
        <?php endif; ?>
        
        <h3>Historique des emprunts</h3>
        <?php if (count($history) > 0): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Livre</th>
                        <th>Date d'emprunt</th>
                        <th>Date de retour</th>
                        <th>Durée</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td>
                                <a href="../book_detail.php?id=<?= $item['book_id'] ?>">
                                    <?= htmlspecialchars($item['title']) ?>
                                </a>
                            </td>
                            <td><?= date('d/m/Y', strtotime($item['borrow_date'])) ?></td>
                            <td><?= date('d/m/Y', strtotime($item['return_date'])) ?></td>
                            <td>
                                <?php 
                                    $borrowDate = new DateTime($item['borrow_date']);
                                    $returnDate = new DateTime($item['return_date']);
                                    $interval = $borrowDate->diff($returnDate);
                                    echo $interval->days . ' jours';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Vous n'avez aucun emprunt dans votre historique.</p>
        <?php endif; ?>
    </div>
</div>

<?php
include_once "../partials/bottom.php";
?>