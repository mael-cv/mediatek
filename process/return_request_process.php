<?php
session_start();
include_once "../utils/config.php";
include_once "../utils/auth_helper.php";

// Vérifier si l'utilisateur est connecté
if (!isLoggedIn()) {
    $_SESSION['error_message'] = "Vous devez être connecté pour effectuer cette action.";
    header('Location: ../login.php');
    exit;
}

// Vérifier si l'ID de l'emprunt est fourni
if (!isset($_POST['borrow_id']) || empty($_POST['borrow_id'])) {
    $_SESSION['error_message'] = "Identifiant d'emprunt non valide.";
    header('Location: ../user/dashboard.php');
    exit;
}

$borrowId = $_POST['borrow_id'];
$userId = $_SESSION['user_id'];
$db = getDbConnection();

// Vérifier que l'emprunt appartient bien à l'utilisateur et est en statut "borrowed"
$stmt = $db->prepare("
    SELECT b.*, u.*, bk.* 
    FROM borrows b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    WHERE b.id = ? AND b.user_id = ? AND b.status = 'borrowed'
");
$stmt->execute([$borrowId, $userId]);
$borrow = $stmt->fetch();

if (!$borrow) {
    $_SESSION['error_message'] = "Impossible de traiter cette demande de retour.";
    header('Location: ../user/dashboard.php');
    exit;
}

// Mettre à jour le statut de l'emprunt
$stmt = $db->prepare("UPDATE borrows SET status = 'pending_return', return_request_date = NOW() WHERE id = ?");
$result = $stmt->execute([$borrowId]);

if ($result) {
    // Envoyer un email de confirmation de demande de retour
    include_once "../utils/email_helper.php";
    $returnRequestDate = date('Y-m-d');
    sendReturnRequestEmail($borrow, $borrow, $returnRequestDate);
    
    $_SESSION['success_message'] = "Votre demande de retour a été enregistrée. Un administrateur va la traiter prochainement.";
    header('Location: ../user/dashboard.php');
    exit;
} else {
    $_SESSION['error_message'] = "Une erreur est survenue lors de la demande de retour.";
    header('Location: ../user/dashboard.php');
    exit;
}
?>