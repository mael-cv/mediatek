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

// Vérifier si la méthode est POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../405.php');
    exit;
}

// Vérifier le token CSRF
if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
    echo "<div class='error-message'>Erreur de sécurité: formulaire invalide. Veuillez réessayer.</div>";
    echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Récupérer l'ID de l'utilisateur
$userId = isset($_POST['id']) ? intval($_POST['id']) : 0;

// Vérifier si l'ID est valide
if (!$userId) {
    echo "<div class='error-message'>ID d'utilisateur invalide.</div>";
    echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

try {
    $db = getDbConnection();
    
    // Vérifier si l'utilisateur existe
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        echo "<div class='error-message'>Utilisateur non trouvé.</div>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Vérifier si l'utilisateur essaie de supprimer son propre compte
    if ($userId == $_SESSION['user_id']) {
        echo "<div class='error-message'>Vous ne pouvez pas supprimer votre propre compte.</div>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Supprimer l'utilisateur
    $deleteStmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $result = $deleteStmt->execute([$userId]);
    
    if ($result) {
        echo "<div class='success-message'>L'utilisateur a été supprimé avec succès!</div>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    } else {
        echo "<div class='error-message'>Une erreur s'est produite lors de la suppression de l'utilisateur.</div>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
}

include_once "./partials/bottom.php";
?>