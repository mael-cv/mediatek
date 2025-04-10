<?php
/**
 * Fonctions d'authentification et de sécurité
 */

/**
 * Initialise une session sécurisée
 */
function initSecureSession() {
    $sessionName = 'MEDIATEK_SESSION';
    $secure = (ENVIRONMENT === 'production');
    $httponly = true;
    
    // Force PHP to use cookies for session
    ini_set('session.use_only_cookies', 1);
    
    // Prevents session fixation attacks
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    
    // Use custom session name
    session_name($sessionName);
    
    // Set session cookie parameters
    session_set_cookie_params([
        'lifetime' => 3600,        // 1 hour
        'path' => '/',             // Available in entire domain
        'domain' => '',            // Set to host domain in production
        'secure' => $secure,       // Only send over HTTPS if available
        'httponly' => $httponly,   // Prevent JavaScript access
        'samesite' => 'Lax'        // Prevent CSRF
    ]);
    
    // Start the session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Génère un token CSRF et l'enregistre en base de données
 */
function generateCsrfToken() {
    $token = bin2hex(random_bytes(32)); // 64 caractères aléatoires
    
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("INSERT INTO csrf_tokens (token, session_id, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))");
        $stmt->execute([$token, session_id()]);
        
        $_SESSION['csrf_token'] = $token;
        return $token;
    } catch (PDOException $e) {
        // En cas d'erreur, générer un token de session uniquement (moins sécurisé mais fonctionnel)
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        return $token;
    }
}

/**
 * Vérifie la validité d'un token CSRF
 */
function verifyCsrfToken($token) {
    if (empty($token) || empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        return false;
    }
    
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT token FROM csrf_tokens WHERE token = ? AND session_id = ? AND expires_at > NOW()");
        $stmt->execute([$token, session_id()]);
        
        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        // En cas d'erreur DB, vérifier uniquement le token de session
        return ($token === $_SESSION['csrf_token']);
    }
}

/**
 * Nettoie les tokens CSRF expirés
 */
function cleanupCsrfTokens() {
    try {
        $db = getDbConnection();
        $db->exec("DELETE FROM csrf_tokens WHERE expires_at < NOW()");
    } catch (PDOException $e) {
        // Ignorer les erreurs, ce n'est pas critique
    }
}

/**
 * Vérifie si l'utilisateur est authentifié
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function isLoggedIn() {
    return isAuthenticated();
}

/**
 * Vérifie si l'utilisateur connecté est admin
 */
function isAdmin() {
    return isAuthenticated() && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Redirige vers la page de connexion si non connecté
 */
function requireLogin() {
    if (!isAuthenticated()) {
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

/**
 * Redirige vers l'accueil si non admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: /index.php?error=unauthorized');
        exit;
    }
}

/**
 * Enregistre une tentative de connexion échouée
 */
function recordFailedLogin($email) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE users SET failed_login_attempts = failed_login_attempts + 1, 
                             last_login_attempt = NOW() WHERE email = ?");
        $stmt->execute([$email]);
        
        // Verrouiller le compte après 5 tentatives échouées
        $stmt = $db->prepare("UPDATE users SET account_locked = TRUE 
                             WHERE email = ? AND failed_login_attempts >= 5");
        $stmt->execute([$email]);
    } catch (PDOException $e) {
        // Log error silently
    }
}

/**
 * Vérifie si le compte est verrouillé
 */
function isAccountLocked($email) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT account_locked, last_login_attempt FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Utilisateur non trouvé - on retourne false pour éviter une divulgation d'information
            return false;
        }
        
        if ($user['account_locked']) {
            // Si le compte est verrouillé depuis plus de 15 minutes, on le déverrouille
            $lastAttempt = strtotime($user['last_login_attempt']);
            if (time() - $lastAttempt > 900) { // 900 secondes = 15 minutes
                $resetStmt = $db->prepare("UPDATE users SET account_locked = FALSE, failed_login_attempts = 0 WHERE email = ?");
                $resetStmt->execute([$email]);
                return false;
            }
            return true;
        }
        
        return false;
    } catch (PDOException $e) {
        // En cas d'erreur, on suppose que le compte n'est pas verrouillé
        return false;
    }
}

/**
 * Réinitialise les tentatives de connexion après succès
 */
function resetLoginAttempts($email) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("UPDATE users SET failed_login_attempts = 0, account_locked = FALSE WHERE email = ?");
        $stmt->execute([$email]);
    } catch (PDOException $e) {
        // Ignorer les erreurs, ce n'est pas critique
    }
}

/**
 * Enregistre une tentative de connexion suspecte par IP
 */
function recordSuspiciousIpAttempt($ip) {
    try {
        $db = getDbConnection();
        
        // Vérifier si l'IP existe déjà
        $stmt = $db->prepare("SELECT * FROM ip_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        
        if ($stmt->rowCount() > 0) {
            // Augmenter le compteur et mettre à jour la dernière tentative
            $stmt = $db->prepare("UPDATE ip_attempts 
                                 SET attempt_count = attempt_count + 1, 
                                     last_attempt = NOW() 
                                 WHERE ip_address = ?");
            $stmt->execute([$ip]);
            
            // Vérifier si on doit bloquer l'IP (10+ tentatives en moins d'une heure)
            $stmt = $db->prepare("SELECT * FROM ip_attempts 
                                 WHERE ip_address = ? 
                                 AND attempt_count >= 10 
                                 AND TIMESTAMPDIFF(MINUTE, first_attempt, NOW()) <= 60");
            $stmt->execute([$ip]);
            
            if ($stmt->rowCount() > 0) {
                // Bloquer l'IP pour 2 heures
                $stmt = $db->prepare("UPDATE ip_attempts 
                                     SET is_blocked = TRUE, 
                                         block_expires = DATE_ADD(NOW(), INTERVAL 2 HOUR) 
                                     WHERE ip_address = ?");
                $stmt->execute([$ip]);
                
                // Enregistrer l'événement dans les logs
                error_log("IP bloquée pour activité suspecte: $ip");
            }
        } else {
            // Première tentative pour cette IP
            $stmt = $db->prepare("INSERT INTO ip_attempts (ip_address) VALUES (?)");
            $stmt->execute([$ip]);
        }
    } catch (PDOException $e) {
        // Ignorer les erreurs, mais les journaliser
        error_log("Erreur lors de l'enregistrement de tentative IP: " . $e->getMessage());
    }
}

/**
 * Vérifie si une IP est bloquée
 */
function isIpBlocked($ip) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("SELECT * FROM ip_attempts 
                             WHERE ip_address = ? 
                             AND is_blocked = TRUE 
                             AND block_expires > NOW()");
        $stmt->execute([$ip]);
        
        return ($stmt->rowCount() > 0);
    } catch (PDOException $e) {
        // En cas d'erreur, supposer que l'IP n'est pas bloquée
        error_log("Erreur lors de la vérification de blocage IP: " . $e->getMessage());
        return false;
    }
}

/**
 * Réinitialiser les tentatives d'une IP après une connexion réussie
 */
function resetIpAttempts($ip) {
    try {
        $db = getDbConnection();
        $stmt = $db->prepare("DELETE FROM ip_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
    } catch (PDOException $e) {
        // Ignorer les erreurs
        error_log("Erreur lors de la réinitialisation des tentatives IP: " . $e->getMessage());
    }
}
?>
