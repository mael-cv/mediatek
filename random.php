<?php
// Include database connection
include_once "./utils/config.php";

try {
    // Get database connection
    $db = getDbConnection();
    
    // Query to select a random book from the database
    $stmt = $db->query("SELECT id FROM books ORDER BY RAND() LIMIT 1");
    $randomBook = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($randomBook) {
        // Redirect to the book details page with the random book ID
        header("Location: book_detail.php?id=" . $randomBook['id']);
        exit;
    } else {
        // If no books in database, redirect to catalog
        header("Location: catalog.php?error=no_books");
        exit;
    }
} catch (PDOException $e) {
    // Log error (but don't display to user)
    error_log("Random book error: " . $e->getMessage());
    // Redirect to catalog with error
    header("Location: catalog.php?error=db_error");
    exit;
}
?>