<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get the order ID from the URL
$order_id = $_GET['order_id'];

// Get order details
$query = "SELECT oi.order_item_id, i.name, i.shop_id FROM order_items oi 
          JOIN items i ON oi.item_id = i.item_id 
          WHERE oi.order_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get feedback data
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    // Get the shop_id for the order (assuming all items in the order belong to the same shop)
    $shop_id = $order_items[0]['shop_id']; // Assuming all items belong to the same shop

    // Insert feedback into the database
    $query_feedback = "INSERT INTO feedback (user_id, order_id, shop_id, rating, comment) 
                       VALUES (?, ?, ?, ?, ?)";
    $stmt_feedback = $pdo->prepare($query_feedback);
    $stmt_feedback->execute([$_SESSION['user_id'], $order_id, $shop_id, $rating, $comment]);

    echo "Thank you for your feedback!";
    header("Location: orders.php");
    exit;
}
?>

<?php include 'components/header.php'; ?>
<section class="feedback-section">
    <h1 class="feedback-page-title">Leave Feedback</h1>

    <form method="POST" action="feedback.php?order_id=<?= $order_id ?>" class="feedback-form">
        <h3 class="order-items-title">Order Items</h3>
        <ul class="order-items-list">
            <?php foreach ($order_items as $order_item): ?>
                <li class="order-item">
                    <label for="item_id" class="item-name"><?= $order_item['name'] ?></label>
                    <input type="radio" name="item_id" value="<?= $order_item['order_item_id'] ?>" required class="item-radio">
                    <br>
                    <label for="rating" class="rating-label">Rating:</label>
                    <select name="rating" required class="rating-select">
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                    <br>
                    <label for="comment" class="comment-label">Comment:</label><br>
                    <textarea name="comment" rows="4" required class="comment-textarea"></textarea><br>
                </li>
            <?php endforeach; ?>
        </ul>
        <button type="submit" class="submit-feedback-btn">Submit Feedback</button>
    </form>
</section>


<?php include 'components/footer.php'; ?>