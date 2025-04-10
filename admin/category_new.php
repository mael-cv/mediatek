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
    echo "<p><a href='category_new_form.php'>Retour au formulaire de création</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Vérifier les données du formulaire
$errors = [];

if (!isset($_POST['name']) || trim($_POST['name']) === '') {
    $errors[] = "Le nom de la catégorie est obligatoire.";
}

// Si des erreurs sont détectées, les afficher
if (count($errors) > 0) {
    echo "<div class='error-message'><ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul></div>";
    echo "<p><a href='category_new_form.php'>Retour au formulaire de création</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Récupérer les données du formulaire
$name = trim($_POST['name']);
$description = isset($_POST['description']) ? trim($_POST['description']) : null;

try {
    $db = getDbConnection();
    
    // Vérifier si la catégorie existe déjà
    $stmt = $db->prepare("SELECT id FROM categories WHERE name = ?");
    $stmt->execute([$name]);
    if ($stmt->rowCount() > 0) {
        echo "<div class='error-message'>Une catégorie avec ce nom existe déjà.</div>";
        echo "<p><a href='category_new_form.php'>Retour au formulaire de création</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Insérer la nouvelle catégorie
    $stmt = $db->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $result = $stmt->execute([$name, $description]);
    
    if ($result) {
        $_SESSION['success_message'] = "La catégorie a été créée avec succès!";
        header('Location: category_index.php');
        exit;
    } else {
        echo "<div class='error-message'>Une erreur s'est produite lors de la création de la catégorie.</div>";
        echo "<p><a href='category_new_form.php'>Retour au formulaire de création</a></p>";
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='category_new_form.php'>Retour au formulaire de création</a></p>";
}

include_once "./partials/bottom.php";
?>