<?php
session_start();
include_once "./utils/config.php";
include_once "./utils/auth_helper.php";

// Vérifier si l'ID du livre est fourni
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: catalog.php');
    exit;
}

$bookId = $_GET['id'];
$db = getDbConnection();

// Récupérer les informations du livre
$stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$bookId]);
$book = $stmt->fetch();

if (!$book) {
    header('Location: catalog.php');
    exit;
}

// Vérifier si l'utilisateur connecté a emprunté ce livre
$userBorrowed = false;
$borrowId = null;
$borrowStatus = null;

if (isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $stmt = $db->prepare("
        SELECT id, status FROM borrows 
        WHERE book_id = ? AND user_id = ? 
        AND (status = 'borrowed' OR status = 'pending_return')
    ");
    $stmt->execute([$bookId, $userId]);
    $borrow = $stmt->fetch();
    
    if ($borrow) {
        $userBorrowed = true;
        $borrowId = $borrow['id'];
        $borrowStatus = $borrow['status'];
    }
}

include_once "./partials/top.php";
?>

<div class="container book-detail">
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <div class="book-header">
        <div class="book-cover">
            <img src="<?= !empty($book['cover_image']) ? $book['cover_image'] : './assets/images/default-cover.jpg' ?>" 
                 alt="<?= htmlspecialchars($book['title']) ?>">
        </div>
        
        <div class="book-info">
            <h2><?= htmlspecialchars($book['title']) ?></h2>
            <p class="author">par <strong><?= htmlspecialchars($book['author']) ?></strong></p>
            
            <?php if (!empty($book['isbn'])): ?>
                <p class="isbn">ISBN: <?= htmlspecialchars($book['isbn']) ?></p>
            <?php endif; ?>
            
            <p class="category">Catégorie: <?= htmlspecialchars($book['category']) ?></p>
            
            <div class="availability">
                <?php if ($userBorrowed): ?>
                    <p class="user-borrowed">Vous avez emprunté ce livre</p>
                    <?php if ($borrowStatus == 'borrowed'): ?>
                        <form action="./process/return_request_process.php" method="post">
                            <input type="hidden" name="borrow_id" value="<?= $borrowId ?>">
                            <button type="submit" class="return-button">Demander le retour</button>
                        </form>
                    <?php elseif ($borrowStatus == 'pending_return'): ?>
                        <p class="pending-return">Retour en attente de validation</p>
                    <?php endif; ?>
                <?php elseif ($book['available']): ?>
                    <p class="available">Disponible</p>
                    <?php if (isLoggedIn()): ?>
                        <form action="./process/borrow_process.php" method="post">
                            <input type="hidden" name="book_id" value="<?= $book['id'] ?>">
                            <button type="submit" class="borrow-button">Emprunter</button>
                        </form>
                    <?php else: ?>
                        <p><a href="./login.php">Connectez-vous</a> pour emprunter ce livre</p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="unavailable">Indisponible</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="book-description">
        <h3>Description</h3>
        <p><?= nl2br(htmlspecialchars($book['description'])) ?></p>
    </div>
    
    <div class="back-link">
        <a href="./catalog.php"><i class="light-icon-arrow-left"></i> Retour au catalogue</a>
    </div>
</div>

<?php include_once "./partials/bottom.php"; ?>