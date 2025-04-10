<?php
// Utiliser un chemin absolu pour inclure auth.php
include_once __DIR__ . "/../utils/auth.php";

// Déterminer le chemin relatif vers la racine
$rootPath = '';
if (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    $rootPath = '../';
} elseif (strpos($_SERVER['REQUEST_URI'], '/auth/') !== false) {
    $rootPath = '../';
} elseif (strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
    $rootPath = '../';
}

// Définir les variables d'état d'authentification
$isUserLoggedIn = isAuthenticated();
$isUserAdmin = isAdmin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediaTek - Bibliothèque numérique</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="<?= $rootPath ?>assets/css/custom.css">
    <link rel="stylesheet" href="<?= $rootPath ?>assets/css/light-icons.css">
</head>
<body>
    <nav class="container-fluid">
        <ul>
            <li><a href="<?= $rootPath ?>index.php" class="logo">MediaTek</a></li>
        </ul>
        <ul>
            <li><a href="<?= $rootPath ?>random.php">Random Dice</a></li>
            <li><a href="<?= $rootPath ?>about.php">À propos</a></li>
            <?php if ($isUserLoggedIn): ?>
                <?php if ($isUserAdmin): ?>
                    <li><a href="<?= $rootPath ?>admin/index.php" class="admin-link">Administration</a></li>
                <?php else: ?>
                    <li><a href="<?= $rootPath ?>user/dashboard.php">Mon espace</a></li>
                <?php endif; ?>
                <li><a href="<?= $rootPath ?>admin/profile.php">Mon profil</a></li>
                <li><a href="<?= $rootPath ?>auth/logout.php">Déconnexion</a></li>
            <?php else: ?>
                <li><a href="<?= $rootPath ?>auth/login.php">Connexion</a></li>
                <li><a href="<?= $rootPath ?>auth/register.php">Inscription</a></li>
            <?php endif; ?>
        </ul>
    </nav>
    <main class="container">