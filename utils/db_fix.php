<?php
include_once "config.php";
include_once "auth.php";

// Initialiser la session sécurisée
initSecureSession();

// Vérifier si l'utilisateur est un admin
if (!isAdmin()) {
    // Rediriger vers la page de connexion avec un message
    $_SESSION['error_message'] = "Accès restreint. Veuillez vous connecter avec un compte administrateur.";
    header('Location: ../auth/login.php');
    exit;
}

try {
    $db = getDbConnection();
    
    // Vérifier si la table books_categories existe, sinon la créer
    $checkTable = $db->query("SHOW TABLES LIKE 'books_categories'");
    if ($checkTable->rowCount() == 0) {
        $db->exec("CREATE TABLE books_categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            book_id INT NOT NULL,
            category_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
            UNIQUE KEY book_category_unique (book_id, category_id)
        )");
        echo "Table books_categories créée avec succès.<br>";
    } else {
        echo "La table books_categories existe déjà.<br>";
    }
    
    // Vérifier la structure de la table borrows
    $checkBorrows = $db->query("DESCRIBE borrows");
    $columns = $checkBorrows->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('user_id', $columns)) {
        $db->exec("ALTER TABLE borrows ADD COLUMN user_id INT NOT NULL AFTER id");
        $db->exec("ALTER TABLE borrows ADD FOREIGN KEY (user_id) REFERENCES users(id)");
        echo "Colonne user_id ajoutée à la table borrows.<br>";
    } else {
        echo "La colonne user_id existe déjà dans la table borrows.<br>";
    }
    
    if (!in_array('book_id', $columns)) {
        $db->exec("ALTER TABLE borrows ADD COLUMN book_id INT NOT NULL AFTER user_id");
        $db->exec("ALTER TABLE borrows ADD FOREIGN KEY (book_id) REFERENCES books(id)");
        echo "Colonne book_id ajoutée à la table borrows.<br>";
    } else {
        echo "La colonne book_id existe déjà dans la table borrows.<br>";
    }
    
    echo "<p>Toutes les modifications de la base de données ont été effectuées avec succès.</p>";
    echo "<p><a href='../admin/index.php'>Retour au tableau de bord</a></p>";
    
} catch (PDOException $e) {
    echo "Erreur lors de la mise à jour de la base de données: " . htmlspecialchars($e->getMessage());
}
?>