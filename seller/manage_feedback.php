<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch seller's shop details
$query = "SELECT * FROM shops WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$seller_id]);
$shop = $stmt->fetch();

// Fetch feedback for the seller's shop
$query_feedback = "SELECT f.*, o.user_id AS buyer_id, u.username FROM feedback f
                   JOIN orders o ON f.order_id = o.order_id
                   JOIN users u ON o.user_id = u.user_id
                   WHERE f.shop_id = ?";
$stmt_feedback = $pdo->prepare($query_feedback);
$stmt_feedback->execute([$shop['shop_id']]);
$feedback = $stmt_feedback->fetchAll();
?>

<?php include 'components/header.php'; ?>

<main class="dashboard-content">
    <h1>Manage Feedback</h1>
    <div class="dashboard-content">
        <h2>Feedback for Your Shop</h2>
        <ul>
            <?php foreach ($feedback as $fb): ?>
                <li>
                    <strong>Buyer: <?= $fb['username'] ?> (Rating: <?= $fb['rating'] ?>/5)</strong><br>
                    Comment: <?= $fb['comment'] ?><br>
                    Date: <?= $fb['created_at'] ?><br>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</main>
<?php include 'components/footer.php'; ?>