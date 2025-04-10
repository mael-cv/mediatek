<?php
include_once "../utils/config.php";
include_once "./partials/top.php";
include_once "../utils/auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isAuthenticated()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer les informations de l'utilisateur connecté
$userId = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<div class='error-message'>Utilisateur non trouvé.</div>";
        include_once "./partials/bottom.php";
        exit;
    }
    
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
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    include_once "./partials/bottom.php";
    exit;
}

// Message de succès
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}
?>

<h2>Mon profil</h2>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-info">
            <h3><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h3>
            <p><?= htmlspecialchars($user['email']) ?></p>
            <p><strong>Membre depuis:</strong> <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
        </div>
        <div class="profile-actions">
            <a href="profile_edit.php" class="btn btn-primary">
                <i class="light-icon-pencil"></i> Modifier mon profil
            </a>
            <a href="profile_password.php" class="btn btn-secondary">
                <i class="light-icon-lock"></i> Changer mon mot de passe
            </a>
        </div>
    </div>

    <div class="profile-details">
        <div class="detail-section">
            <h3>Informations personnelles</h3>
            <p><strong>Adresse:</strong> <?= $user['address'] ? htmlspecialchars($user['address']) : '<em>Non renseignée</em>' ?></p>
            <p><strong>Code postal:</strong> <?= $user['zip_code'] ? htmlspecialchars($user['zip_code']) : '<em>Non renseigné</em>' ?></p>
            <p><strong>Date de naissance:</strong> <?= $user['birth_date'] ? date('d/m/Y', strtotime($user['birth_date'])) : '<em>Non renseignée</em>' ?></p>
        </div>

        <div class="detail-section">
            <h3>Statut du compte</h3>
            <p><strong>Administrateur:</strong> <?= $user['is_admin'] ? 'Oui' : 'Non' ?></p>
            <p><strong>Email vérifié:</strong> <?= $user['email_verified'] ? 'Oui' : 'Non' ?></p>
            <p><strong>Dernière connexion:</strong> <?= $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : '<em>Jamais</em>' ?></p>
        </div>
    </div>

    <div class="borrows-section">
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
include_once "./partials/bottom.php";
?>