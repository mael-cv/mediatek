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

// Récupérer les données du formulaire
$userId = isset($_POST['id']) ? intval($_POST['id']) : 0;
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
$lastName = trim($_POST['last_name'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$address = trim($_POST['address'] ?? '');
$zipCode = trim($_POST['zip_code'] ?? '');
$birthDate = $_POST['birth_date'] ?? null;
$isAdmin = isset($_POST['is_admin']) && $_POST['is_admin'] == '1';
$emailVerified = isset($_POST['email_verified']) && $_POST['email_verified'] == '1';
$accountLocked = isset($_POST['account_locked']) && $_POST['account_locked'] == '1';
$newPassword = trim($_POST['new_password'] ?? '');

// Validation des données
$errors = [];

if (!$userId) {
    $errors[] = "ID d'utilisateur invalide.";
}

if (!$email) {
    $errors[] = "Email invalide.";
}

if (empty($lastName)) {
    $errors[] = "Le nom est obligatoire.";
}

if (empty($firstName)) {
    $errors[] = "Le prénom est obligatoire.";
}

// Vérifier si le nouveau mot de passe respecte les critères
if (!empty($newPassword) && strlen($newPassword) < 8) {
    $errors[] = "Le mot de passe doit contenir au moins 8 caractères.";
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
    echo "<p><a href='user_edit_form.php?id=" . $userId . "'>Retour au formulaire d'édition</a></p>";
    include_once "./partials/bottom.php";
    exit;
}

try {
    $db = getDbConnection();
    
    // Vérifier si l'email est déjà utilisé par un autre utilisateur
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $userId]);
    if ($stmt->rowCount() > 0) {
        echo "<div class='error-message'>Cette adresse email est déjà utilisée par un autre utilisateur.</div>";
        echo "<p><a href='user_edit_form.php?id=" . $userId . "'>Retour au formulaire d'édition</a></p>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Mise à jour des données de l'utilisateur
    if (!empty($newPassword)) {
        // Si un nouveau mot de passe est fourni, le hasher et mettre à jour
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET 
                             email = ?, 
                             last_name = ?, 
                             first_name = ?, 
                             address = ?, 
                             zip_code = ?, 
                             birth_date = ?, 
                             is_admin = ?, 
                             email_verified = ?, 
                             account_locked = ?,
                             password = ?,
                             failed_login_attempts = 0
                             WHERE id = ?");
        $result = $stmt->execute([
            $email, $lastName, $firstName, $address, $zipCode, $birthDate, 
            $isAdmin ? 1 : 0, $emailVerified ? 1 : 0, $accountLocked ? 1 : 0, 
            $hashedPassword, $userId
        ]);
    } else {
        // Sinon, mettre à jour sans changer le mot de passe
        $stmt = $db->prepare("UPDATE users SET 
                             email = ?, 
                             last_name = ?, 
                             first_name = ?, 
                             address = ?, 
                             zip_code = ?, 
                             birth_date = ?, 
                             is_admin = ?, 
                             email_verified = ?, 
                             account_locked = ?
                             WHERE id = ?");
        $result = $stmt->execute([
            $email, $lastName, $firstName, $address, $zipCode, $birthDate, 
            $isAdmin ? 1 : 0, $emailVerified ? 1 : 0, $accountLocked ? 1 : 0, 
            $userId
        ]);
    }
    
    if ($result) {
        echo "<div class='success-message'>L'utilisateur a été mis à jour avec succès!</div>";
        echo "<p><a href='user_show.php?id=" . $userId . "'>Voir les détails de l'utilisateur</a></p>";
        echo "<p><a href='user_index.php'>Retour à la liste des utilisateurs</a></p>";
    } else {
        echo "<div class='error-message'>Une erreur s'est produite lors de la mise à jour de l'utilisateur.</div>";
        echo "<p><a href='user_edit_form.php?id=" . $userId . "'>Retour au formulaire d'édition</a></p>";
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<p><a href='user_edit_form.php?id=" . $userId . "'>Retour au formulaire d'édition</a></p>";
}

include_once "./partials/bottom.php";
?>