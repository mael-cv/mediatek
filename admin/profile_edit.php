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

// Récupérer les informations de l'utilisateur connecté
$userId = $_SESSION['user_id'];

try {
    $db = getDbConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();

    if (!$user) {
        echo "<div class='error-message'>Utilisateur non trouvé.</div>";
        include_once "./partials/bottom.php";
        exit;
    }
} catch (PDOException $e) {
    echo "<div class='error-message'>Erreur de base de données: " . htmlspecialchars($e->getMessage()) . "</div>";
    include_once "./partials/bottom.php";
    exit;
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérifier le token CSRF
    if (!isset($_POST['csrf_token']) || !verifyCsrfToken($_POST['csrf_token'])) {
        echo "<div class='error-message'>Erreur de sécurité: formulaire invalide. Veuillez réessayer.</div>";
        include_once "./partials/bottom.php";
        exit;
    }
    
    // Récupérer et valider les données
    $firstName = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : null;
    $zipCode = isset($_POST['zip_code']) ? trim($_POST['zip_code']) : null;
    $birthDate = isset($_POST['birth_date']) && !empty($_POST['birth_date']) ? $_POST['birth_date'] : null;
    
    $errors = [];
    
    // Validation
    if (empty($firstName)) {
        $errors[] = "Le prénom est obligatoire.";
    }
    
    if (empty($lastName)) {
        $errors[] = "Le nom est obligatoire.";
    }
    
    // Si pas d'erreurs, mettre à jour l'utilisateur
    if (empty($errors)) {
        try {
            $stmt = $db->prepare("
                UPDATE users SET 
                first_name = ?, 
                last_name = ?, 
                address = ?, 
                zip_code = ?, 
                birth_date = ?
                WHERE id = ?
            ");
            $result = $stmt->execute([$firstName, $lastName, $address, $zipCode, $birthDate, $userId]);
            
            if ($result) {
                $_SESSION['success_message'] = "Votre profil a été mis à jour avec succès!";
                header('Location: profile.php');
                exit;
            } else {
                $errors[] = "Une erreur s'est produite lors de la mise à jour du profil.";
            }
        } catch (PDOException $e) {
            $errors[] = "Erreur de base de données: " . $e->getMessage();
        }
    }
}
?>

<h2>Modifier mon profil</h2>

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

    <form method="POST" action="profile_edit.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generateCsrfToken()) ?>">
        
        <div class="form-block">
            <label for="first_name">Prénom *</label>
            <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required>
        </div>
        
        <div class="form-block">
            <label for="last_name">Nom *</label>
            <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required>
        </div>
        
        <div class="form-block">
            <label for="address">Adresse</label>
            <textarea id="address" name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
        </div>
        
        <div class="form-block">
            <label for="zip_code">Code postal</label>
            <input type="text" id="zip_code" name="zip_code" value="<?= htmlspecialchars($user['zip_code'] ?? '') ?>">
        </div>
        
        <div class="form-block">
            <label for="birth_date">Date de naissance</label>
            <input type="date" id="birth_date" name="birth_date" value="<?= $user['birth_date'] ?? '' ?>">
        </div>
        
        <input type="submit" value="Enregistrer les modifications">
    </form>
    
    <div class="form-actions">
        <a href="profile.php" class="btn btn-secondary">Annuler</a>
    </div>
</div>

<?php
include_once "./partials/bottom.php";
?>