<?php
session_start();
include_once "../utils/config.php";
include_once "../utils/auth_helper.php";

// Vérifier si l'utilisateur est un admin
if (!isAdmin()) {
    $_SESSION['error_message'] = "Accès refusé.";
    header('Location: ../login.php');
    exit;
}

$db = getDbConnection();

// Récupérer les demandes de retour en attente
$stmt = $db->prepare("
    SELECT b.id as borrow_id, b.borrow_date, b.due_date, b.return_request_date, 
           u.id as user_id, u.first_name, u.last_name, u.email,
           bk.id as book_id, bk.title, bk.author, bk.isbn
    FROM borrows b
    JOIN users u ON b.user_id = u.id
    JOIN books bk ON b.book_id = bk.id
    WHERE b.status = 'pending_return'
    ORDER BY b.return_request_date ASC
");
$stmt->execute();
$pendingReturns = $stmt->fetchAll();

include_once "../partials/admin_top.php";
?>

<div class="container">
    <h2>Gestion des retours</h2>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
            echo $_SESSION['success_message']; 
            unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <h3>Demandes de retour en attente</h3>
    <?php if (count($pendingReturns) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Livre</th>
                    <th>Utilisateur</th>
                    <th>Date d'emprunt</th>
                    <th>Date limite</th>
                    <th>Demande le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pendingReturns as $return): ?>
                    <tr>
                        <td>
                            <a href="../book_detail.php?id=<?= $return['book_id'] ?>">
                                <?= htmlspecialchars($return['title']) ?>
                            </a>
                            <small>(ISBN: <?= htmlspecialchars($return['isbn']) ?>)</small>
                        </td>
                        <td>
                            <?= htmlspecialchars($return['first_name'] . ' ' . $return['last_name']) ?>
                            <small>(<?= htmlspecialchars($return['email']) ?>)</small>
                        </td>
                        <td><?= date('d/m/Y', strtotime($return['borrow_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($return['due_date'])) ?></td>
                        <td><?= date('d/m/Y', strtotime($return['return_request_date'])) ?></td>
                        <td>
                            <form action="../process/validate_return_process.php" method="post">
                                <input type="hidden" name="borrow_id" value="<?= $return['borrow_id'] ?>">
                                <button type="submit" class="validate-button">Valider le retour</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune demande de retour en attente.</p>
    <?php endif; ?>
</div>

<?php include_once "../partials/admin_bottom.php"; ?>