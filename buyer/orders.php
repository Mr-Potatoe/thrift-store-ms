<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Check if order cancellation is requested
if (isset($_GET['cancel_order_id'])) {
    $order_id = $_GET['cancel_order_id'];

    // Get the current order status
    $order_status_query = "SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?";
    $stmt_order_status = $pdo->prepare($order_status_query);
    $stmt_order_status->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt_order_status->fetch();

    if ($order && ($order['order_status'] == 'pending' || $order['order_status'] == 'shipped')) {
        // Allow cancellation if the order is "pending" or "shipped"
        $update_query = "UPDATE orders SET order_status = 'canceled' WHERE order_id = ?";
        $stmt_update = $pdo->prepare($update_query);
        $stmt_update->execute([$order_id]);

        echo "Order canceled successfully!";
    } else {
        echo "You cannot cancel this order.";
    }
}

// Get orders for the logged-in user along with feedback and seller's response
$query = "
    SELECT o.*, f.feedback_id, f.rating, f.comment AS feedback_comment, f.response AS seller_response, f.created_at AS feedback_date
    FROM orders o
    LEFT JOIN feedback f ON o.order_id = f.order_id
    WHERE o.user_id = ? ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<?php include 'components/header.php'; ?>
<section class="orders-page">
    <h1>Your Orders</h1>

    <?php if (empty($orders)): ?>
        <p class="no-orders-message">You have no orders yet.</p>
    <?php else: ?>
        <ul class="orders-list">
            <?php foreach ($orders as $order): ?>
                <li>
                    <h3>Order ID: <?= $order['order_id'] ?> - Total: $<?= number_format($order['total_price'], 2) ?></h3>
                    <p>Status: <?= ucfirst($order['order_status']) ?></p>
                    <p>Shipping Address: <?= $order['shipping_address'] ?></p>
                    <p>Order Date: <?= $order['order_date'] ?></p>

                    <h4>Items:</h4>
                    <ul class="order-items-list">
                        <?php
                        $order_items_query = "SELECT i.name, oi.quantity, oi.price FROM order_items oi JOIN items i ON oi.item_id = i.item_id WHERE oi.order_id = ?";
                        $stmt_order_items = $pdo->prepare($order_items_query);
                        $stmt_order_items->execute([$order['order_id']]);
                        $order_items = $stmt_order_items->fetchAll();

                        foreach ($order_items as $item):
                        ?>
                            <li><?= $item['name'] ?> - $<?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></li>
                        <?php endforeach; ?>
                    </ul>

                    <a href="feedback.php?order_id=<?= $order['order_id'] ?>" class="leave-feedback-link">Leave Feedback</a>

                    <!-- Display feedback and seller's response if available -->
                    <?php if ($order['feedback_id']): ?>
                        <div class="feedback-section">
                            <h5>Feedback:</h5>
                            <p>Rating: <?= $order['rating'] ?>/5</p>
                            <p>Comment: <?= htmlspecialchars($order['feedback_comment']) ?></p>
                            <p>Feedback Date: <?= $order['feedback_date'] ?></p>

                            <?php if ($order['seller_response']): ?>
                                <h6>Seller's Response:</h6>
                                <p><?= htmlspecialchars($order['seller_response']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Cancel order button -->
                    <?php if ($order['order_status'] == 'pending' || $order['order_status'] == 'shipped'): ?>
                        <br><br>
                        <a href="?cancel_order_id=<?= $order['order_id'] ?>" onclick="return confirm('Are you sure you want to cancel this order?')" class="cancel-order-link">Cancel Order</a>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</section>

<?php include 'components/footer.php'; ?>
