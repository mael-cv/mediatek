<?php
include_once "../utils/config.php";
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

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../405.php');
    exit;
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    include_once "./partials/top.php";
    echo "<div class='error-message'>Erreur de sécurité: formulaire invalide. Veuillez réessayer.</div>";
    echo "<p><a href='borrow_index.php'>Retour à la liste des emprunts</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Vérifier si borrow_id est présent
if (!isset($_POST['borrow_id']) || !is_numeric($_POST['borrow_id'])) {
    include_once "./partials/top.php";
    echo "<div class='error-message'>ID d'emprunt invalide.</div>";
    echo "<p><a href='borrow_index.php'>Retour à la liste des emprunts</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

$borrowId = intval($_POST['borrow_id']);

try {
    $db = getDbConnection();
    
    // Vérifier si l'emprunt existe et n'est pas déjà retourné
    $checkStmt = $db->prepare("SELECT id FROM borrows WHERE id = ? AND return_date IS NULL");
    $checkStmt->execute([$borrowId]);
    
    if ($checkStmt->rowCount() == 0) {
        include_once "./partials/top.php";
        echo "<div class='error-message'>Cet emprunt n'existe pas ou a déjà été retourné.</div>";
        echo "<p><a href='borrow_index.php'>Retour à la liste des emprunts</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Marquer l'emprunt comme retourné
    $updateStmt = $db->prepare("UPDATE borrows SET return_date = NOW() WHERE id = ?");
    $result = $updateStmt->execute([$borrowId]);
    
    if ($result) {
        // Get the borrowing details to send email notification
        $detailsStmt = $db->prepare("
            SELECT b.*, u.*, bk.* 
            FROM borrows b
            JOIN users u ON b.user_id = u.id
            JOIN books bk ON b.book_id = bk.id
            WHERE b.id = ?
        ");
        $detailsStmt->execute([$borrowId]);
        $details = $detailsStmt->fetch();

        // Get current date for return confirmation
        $returnDate = date('Y-m-d');

        // Send return confirmation email
        include_once "../utils/email_helper.php";
        sendReturnConfirmationEmail($details, $details, $returnDate);

        $_SESSION['success_message'] = "Le livre a été marqué comme retourné avec succès.";
        header('Location: borrow_index.php');
        exit;
    } else {
        include_once "./partials/top.php";
        echo "<div class='error-message'>Une erreur s'est produite lors du retour du livre.</div>";
        echo "<p><a href='borrow_index.php'>Retour à la liste des emprunts</a></p>";
        include_once "./partials/bottom.php";
    }
    
} catch (PDOException $e) {
    include_once "./partials/top.php";
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='borrow_index.php'>Retour à la liste des emprunts</a></p>";
    include_once "./partials/bottom.php";
}
?>