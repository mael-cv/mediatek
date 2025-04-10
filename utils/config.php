<?php
// Charger les variables d'environnement depuis un fichier .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            putenv("$key=$value");
        }
    }
}

// Configuration de la base de données
define('DB_HOST', getenv('DB_HOST') ?: 'mysql-litmaaa.alwaysdata.net');
define('DB_NAME', getenv('DB_NAME') ?: 'litmaaa_mediatek');
define('DB_USER', getenv('DB_USER') ?: 'litmaaa_mediatek');
define('DB_PASS', getenv('DB_PASS') ?: 'Jb84Ldhd43JD9s');
define('ENVIRONMENT', getenv('ENVIRONMENT') ?: 'development');

// Fonction de connexion à la base de données
function getDbConnection() {
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        return new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        error_log('Erreur de connexion à la base de données: ' . $e->getMessage());
        die('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
    }
}
?>