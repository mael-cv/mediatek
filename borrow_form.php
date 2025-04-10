<?php
include_once "./utils/config.php";
include_once "./utils/auth.php";
include_once "./partials/top.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isAuthenticated()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Veuillez vous connecter pour emprunter un livre.";
    header('Location: auth/login.php');
    exit;
}

// Vérifier si un ID est fourni
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: 404.php');
    exit;
}

$bookId = intval($_GET['id']);

// Récupérer les détails du livre
try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch();

    if (!$book) {
        header('Location: 404.php');
        exit;
    }

    // Vérifier si le livre est déjà emprunté
    $borrowStmt = $db->prepare("SELECT * FROM borrows WHERE book_id = ? AND return_date IS NULL");
    $borrowStmt->execute([$bookId]);
    $currentBorrow = $borrowStmt->fetch();
    
    if ($currentBorrow) {
        // Le livre est déjà emprunté
        echo "<div class='error-message'>Ce livre est déjà emprunté.</div>";
        echo "<p><a href='book_detail.php?id=" . $bookId . "'>Retour aux détails du livre</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
} catch (PDOException $e) {
    header('Location: 500.php');
    exit;
}

// Message de succès
if (isset($_SESSION['success_message'])) {
    echo "<div class='success-message'>" . htmlspecialchars($_SESSION['success_message']) . "</div>";
    unset($_SESSION['success_message']);
}
?>

<h2>Emprunter un livre</h2>

<div class="borrow-container">
    <div class="book-info">
        <h3><?= htmlspecialchars($book['title']) ?></h3>
        <p><strong>ISBN:</strong> <?= htmlspecialchars($book['isbn']) ?></p>
        <p><strong>Année de publication:</strong> <?= htmlspecialchars($book['publication_year']) ?></p>
        
        <?php if ($book['cover_path']): ?>
            <img src="<?= htmlspecialchars($book['cover_path']) ?>" alt="Couverture" style="max-width: 200px;">
        <?php endif; ?>
    </div>
    
    <div class="borrow-form">
        <p>Vous êtes sur le point d'emprunter ce livre. Un emprunt est valable pour une durée de 30 jours.</p>
        
        <form method="POST" action="process/borrow_process.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            <input type="hidden" name="book_id" value="<?= $bookId ?>">
            
            <div class="form-actions">
                <input type="submit" value="Confirmer l'emprunt">
                <a href="book_detail.php?id=<?= $bookId ?>" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>