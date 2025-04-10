<?php
include_once "../utils/config.php";
include_once "../utils/auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isAuthenticated()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Veuillez vous connecter pour emprunter un livre.";
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
    $_SESSION['error_message'] = "Erreur de sécurité: formulaire invalide. Veuillez réessayer.";
    header('Location: ../index.php');
    exit;
}

// Vérifier si book_id est présent
if (!isset($_POST['book_id']) || !is_numeric($_POST['book_id'])) {
    $_SESSION['error_message'] = "Identifiant de livre invalide.";
    header('Location: ../index.php');
    exit;
}

$bookId = intval($_POST['book_id']);
$userId = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    
    // Vérifier si le livre existe
    $bookStmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $bookStmt->execute([$bookId]);
    $book = $bookStmt->fetch();
    
    if (!$book) {
        $_SESSION['error_message'] = "Ce livre n'existe pas.";
        header('Location: ../index.php');
        exit;
    }
    
    // Vérifier si le livre est déjà emprunté
    $borrowStmt = $db->prepare("SELECT * FROM borrows WHERE book_id = ? AND return_date IS NULL");
    $borrowStmt->execute([$bookId]);
    $currentBorrow = $borrowStmt->fetch();
    
    if ($currentBorrow) {
        $_SESSION['error_message'] = "Ce livre est déjà emprunté.";
        header('Location: ../book_detail.php?id=' . $bookId);
        exit;
    }
    
    // Insérer le nouvel emprunt
    $stmt = $db->prepare("INSERT INTO borrows (user_id, book_id, borrow_date) VALUES (?, ?, NOW())");
    $result = $stmt->execute([$userId, $bookId]);
    
    if ($result) {
        // Get user and book information for the email
        $userStmt = $db->prepare("SELECT * FROM users WHERE id = ?");
        $userStmt->execute([$userId]);
        $user = $userStmt->fetch();
        
        // Calculate return date (30 days from borrow date)
        $borrowDate = date('Y-m-d');
        $returnDate = date('Y-m-d', strtotime('+30 days'));
        
        // Send confirmation email
        include_once "../utils/email_helper.php";
        sendBorrowConfirmationEmail($user, $book, $borrowDate, $returnDate);
        
        $_SESSION['success_message'] = "Vous avez emprunté ce livre avec succès! Vous devez le retourner dans 30 jours.";
        header('Location: ../admin/profile.php');
        exit;
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de l'emprunt du livre.";
        header('Location: ../book_detail.php?id=' . $bookId);
        exit;
    }
    
} catch (PDOException $e) {
    error_log('Erreur lors de l\'emprunt: ' . $e->getMessage());
    $_SESSION['error_message'] = "Erreur lors de l'emprunt. Veuillez réessayer plus tard.";
    header('Location: ../book_detail.php?id=' . $bookId);
    exit;
}
?>