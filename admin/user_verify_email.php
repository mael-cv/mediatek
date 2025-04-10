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
    $_SESSION['error_message'] = "Erreur de sécurité: formulaire invalide.";
    header('Location: user_index.php');
    exit;
}

// Récupérer l'ID de l'utilisateur
$userId = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Vérifier si l'ID est valide
if (!$userId) {
    $_SESSION['error_message'] = "ID d'utilisateur invalide.";
    header('Location: user_index.php');
    exit;
}

try {
    $db = getDbConnection();
    
    // Marquer l'email comme vérifié et effacer les tokens de vérification
    $stmt = $db->prepare("UPDATE users SET email_verified = 1, verification_token = NULL, verification_expiry = NULL WHERE id = ?");
    $result = $stmt->execute([$userId]);
    
    if ($result) {
        $_SESSION['success_message'] = "L'adresse email de l'utilisateur a été marquée comme vérifiée!";
    } else {
        $_SESSION['error_message'] = "Une erreur s'est produite lors de la vérification de l'email.";
    }
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Erreur de base de données: " . $e->getMessage();
}

// Rediriger vers la page des détails de l'utilisateur
header('Location: user_show.php?id=' . $userId);
exit;
?>