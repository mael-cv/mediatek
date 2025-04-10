<?php
include_once "../utils/config.php";
include_once "./partials/top.php";
include_once "../utils/auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est connecté
if (!isAuthenticated()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Veuillez vous connecter pour accéder à cette page.";
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer l'ID de l'utilisateur connecté
$userId = $_SESSION['user_id'];

$errors = [];
$success = false;

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        $errors[] = "Erreur de sécurité: formulaire invalide. Veuillez réessayer.";
    } else {
        // Récupérer les données du formulaire
        $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
        $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Validation
        if (empty($currentPassword)) {
            $errors[] = "Le mot de passe actuel est obligatoire.";
        }
        
        if (empty($newPassword)) {
            $errors[] = "Le nouveau mot de passe est obligatoire.";
        } elseif (strlen($newPassword) < 8) {
            $errors[] = "Le nouveau mot de passe doit contenir au moins 8 caractères.";
        }
        
        if ($newPassword !== $confirmPassword) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }
        
        // Si pas d'erreurs, vérifier le mot de passe actuel et mettre à jour
        if (empty($errors)) {
            try {
                $db = getDbConnection();
                
                // Récupérer le mot de passe actuel de l'utilisateur
                $stmt = $db->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch();
                
                if (!$user) {
                    $errors[] = "Utilisateur non trouvé.";
                } else {
                    // Vérifier si le mot de passe actuel est correct
                    if (!password_verify($currentPassword, $user['password'])) {
                        $errors[] = "Le mot de passe actuel est incorrect.";
                    } else {
                        // Hasher le nouveau mot de passe
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        
                        // Mettre à jour le mot de passe
                        $updateStmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $result = $updateStmt->execute([$hashedPassword, $userId]);
                        
                        if ($result) {
                            $success = true;
                        } else {
                            $errors[] = "Une erreur s'est produite lors de la mise à jour du mot de passe.";
                        }
                    }
                }
            } catch (PDOException $e) {
                $errors[] = "Erreur de base de données: " . $e->getMessage();
            }
        }
    }
}
?>

<h2>Changer mon mot de passe</h2>

<div class="form-container">
    <?php if (!empty($errors)): ?>
        <div class="error-message">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="success-message">
            <p>Votre mot de passe a été mis à jour avec succès!</p>
            <p><a href="profile.php">Retour au profil</a></p>
        </div>
    <?php else: ?>
        <form method="POST" action="profile_password.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
            
            <div class="form-block">
                <label for="current_password">Mot de passe actuel *</label>
                <input type="password" id="current_password" name="current_password" required>
            </div>
            
            <div class="form-block">
                <label for="new_password">Nouveau mot de passe *</label>
                <input type="password" id="new_password" name="new_password" required>
                <small>8 caractères minimum</small>
            </div>
            
            <div class="form-block">
                <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            
            <input type="submit" value="Changer mon mot de passe">
        </form>
        
        <div class="form-actions">
            <a href="profile.php" class="btn btn-secondary">Annuler</a>
        </div>
    <?php endif; ?>
</div>

<?php
include_once "./partials/bottom.php";
?>