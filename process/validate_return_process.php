<?php
session_start();
include_once "../utils/config.php";
include_once "../utils/auth_helper.php";

// Vérifier si l'utilisateur est un admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès refusé.";
    header('Location: ../login.php');
    exit;
}

// Vérifier si l'ID de l'emprunt est fourni
if (!isset($_POST['borrow_id']) || empty($_POST['borrow_id'])) {
    $_SESSION['error_message'] = "Identifiant d'emprunt non valide.";
    header('Location: ../admin/returns.php');
    exit;
}

$borrowId = $_POST['borrow_id'];
$db = getDbConnection();

// Récupérer les informations de l'emprunt
$stmt = $db->prepare("
    SELECT b.*, u.*, bk.* 
    FROM borrows b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    WHERE b.id = ? AND b.status = 'pending_return'
");
$stmt->execute([$borrowId]);
$borrow = $stmt->fetch();

if (!$borrow) {
    $_SESSION['error_message'] = "Impossible de valider ce retour.";
    header('Location: ../admin/returns.php');
    exit;
}

// Mettre à jour le statut de l'emprunt et la disponibilité du livre
try {
    $db->beginTransaction();
    
    // Mettre à jour le statut de l'emprunt
    $stmt = $db->prepare("UPDATE borrows SET status = 'returned', return_date = NOW() WHERE id = ?");
    $stmt->execute([$borrowId]);
    
    // Mettre à jour la disponibilité du livre
    $stmt = $db->prepare("UPDATE books SET available = 1 WHERE id = ?");
    $stmt->execute([$borrow['book_id']]);
    
    $db->commit();
    
    // Envoyer un email de confirmation de retour
    include_once "../utils/email_helper.php";
    $returnDate = date('Y-m-d');
    sendReturnConfirmationEmail($borrow, $borrow, $returnDate);
    
    $_SESSION['success_message'] = "Le retour du livre a été validé avec succès.";
    header('Location: ../admin/returns.php');
    exit;
} catch (Exception $e) {
    $db->rollBack();
    error_log("Erreur lors de la validation du retour: " . $e->getMessage());
    $_SESSION['error_message'] = "Une erreur est survenue lors de la validation du retour.";
    header('Location: ../admin/returns.php');
    exit;
}
?>