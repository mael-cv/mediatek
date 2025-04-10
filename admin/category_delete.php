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
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Vérifier si l'ID est présent
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    include_once "./partials/top.php";
    echo "<div class='error-message'>ID de catégorie manquant ou invalide.</div>";
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

$categoryId = intval($_POST['id']);

try {
    $db = getDbConnection();
    
    // Vérifier si la catégorie existe
    $checkStmt = $db->prepare("SELECT id FROM categories WHERE id = ?");
    $checkStmt->execute([$categoryId]);
    if ($checkStmt->rowCount() == 0) {
        include_once "./partials/top.php";
        echo "<div class='error-message'>Cette catégorie n'existe pas.</div>";
        echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Commencer une transaction
    $db->beginTransaction();
    
    // Supprimer les associations avec les livres
    $deleteAssocStmt = $db->prepare("DELETE FROM books_categories WHERE category_id = ?");
    $deleteAssocStmt->execute([$categoryId]);
    
    // Supprimer la catégorie
    $deleteStmt = $db->prepare("DELETE FROM categories WHERE id = ?");
    $result = $deleteStmt->execute([$categoryId]);
    
    if ($result) {
        // Confirmer la transaction
        $db->commit();
        
        $_SESSION['success_message'] = "La catégorie a été supprimée avec succès!";
        header('Location: category_index.php');
        exit;
    } else {
        // Annuler la transaction en cas d'échec
        $db->rollBack();
        
        include_once "./partials/top.php";
        echo "<div class='error-message'>Une erreur s'est produite lors de la suppression de la catégorie.</div>";
        echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
    }
} catch (PDOException $e) {
    // Annuler la transaction en cas d'erreur
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    
    include_once "./partials/top.php";
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='category_index.php'>Retour à la liste des catégories</a></p>";
}

include_once "./partials/bottom.php";
?>