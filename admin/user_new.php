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
    echo "<p><a href='user_new_form.php'>Retour au formulaire de création</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

// Récupérer les données du formulaire
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$password = trim($_POST['password'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$zipCode = trim($_POST['zip_code'] ?? '');
$birthDate = $_POST['birth_date'] ?? null;
$isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1';
$emailVerified = isset($_POST['email_verified']) && $_POST['email_verified'] == '1';

// Validation des données
$errors = [];

if (!$email) {
    $errors[] = "Email invalide.";
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
}

if (empty($lastName)) {
    $errors[] = "Le nom est obligatoire.";
}

if (empty($firstName)) {
    $errors[] = "Le prénom est obligatoire.";
}

// Si des erreurs sont détectées, les afficher
if (!empty($errors)) {
    echo "<div class='error-message'>";
    echo "<ul>";
    foreach ($errors as $error) {
        echo "<li>" . htmlspecialchars($error) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
    echo "<p><a href='user_new_form.php'>Retour au formulaire de création</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

try {
    $db = getDbConnection();
    
    // Vérifier si l'email est déjà utilisé
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        echo "<div class='error-message'>Cette adresse email est déjà utilisée.</div>";
        echo "<p><a href='user_new_form.php'>Retour au formulaire de création</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Hasher le mot de passe
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Créer le nouvel utilisateur
    $stmt = $db->prepare("INSERT INTO users (email, password, last_name, first_name, address, zip_code, birth_date, is_admin, email_verified) 
                          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result = $stmt->execute([
        $email, $hashedPassword, $lastName, $firstName, $address, $zipCode, $birthDate, 
        $isAdmin ? 1 : 0, $emailVerified ? 1 : 0
    ]);
    
    if ($result) {
        $newUserId = $db->lastInsertId();
        echo "<div class='success-message'>L'utilisateur a été créé avec succès!</div>";
        echo "<p><a href='user_show.php?id=" . $newUserId . "'>Voir les détails de l'utilisateur</a></p>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    } else {
        echo "<div class='error-message'>Une erreur s'est produite lors de la création de l'utilisateur.</div>";
        echo "<p><a href='user_new_form.php'>Retour au formulaire de création</a></p>";
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='user_new_form.php'>Retour au formulaire de création</a></p>";
}

include_once "./partials/bottom.php";
?>