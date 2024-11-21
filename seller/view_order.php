<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : 0;

// Fetch seller's shop details
$query_shop = "SELECT * FROM shops WHERE user_id = ?";
$stmt_shop = $pdo->prepare($query_shop);
$stmt_shop->execute([$seller_id]);
$shop = $stmt_shop->fetch();

// Fetch order details including order items and the buyer's name
$query_order_details = "SELECT o.*, oi.*, i.name AS item_name, i.price AS item_price, i.image AS item_image, u.username AS buyer_name
                        FROM orders o
                        JOIN order_items oi ON o.order_id = oi.order_id
                        JOIN items i ON oi.item_id = i.item_id
                        JOIN users u ON o.user_id = u.user_id
                        WHERE o.order_id = ? AND i.shop_id = ?";
$stmt_order_details = $pdo->prepare($query_order_details);
$stmt_order_details->execute([$order_id, $shop['shop_id']]);
$order_details = $stmt_order_details->fetchAll();

// If the order details are not found, redirect back
if (!$order_details) {
    header("Location: manage_orders.php");
    exit;
}

// Fetch the shipping address and payment status
$order = $order_details[0]; // Assuming there's at least one item in the order

?>

<?php include 'components/header.php'; ?>
<main class="dashboard-content">
<section class="dashboard-section">
    <h1>Order Details for Buyer: <?= htmlspecialchars($order['buyer_name']) ?></h1>

    <h2>Order Information</h2>
    <p><strong>Order Status:</strong> <?= htmlspecialchars($order['order_status']) ?></p>
    <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
    <p><strong>Payment Status:</strong> <?= htmlspecialchars($order['payment_status']) ?></p>

    <h3>Items in this Order</h3>
    <ul>
        <?php foreach ($order_details as $order_item): ?>
            <li>
                <?php if ($order_item['item_image']): ?>
                    <img src="../uploads/items/<?= htmlspecialchars($order_item['item_image']) ?>" alt="<?= htmlspecialchars($order_item['item_name']) ?>" width="100" height="100">
                <?php else: ?>
                    <p>No image available</p>
                <?php endif; ?>
                <strong><?= htmlspecialchars($order_item['item_name']) ?></strong><br>
                Price: $<?= number_format($order_item['item_price'], 2) ?><br>
                Quantity: <?= $order_item['quantity'] ?><br>
                Total: $<?= number_format($order_item['item_price'] * $order_item['quantity'], 2) ?><br>
            </li>
        <?php endforeach; ?>
    </ul>

    <h3>Total Order Price</h3>
    <p><strong>Total Amount: $<?= number_format($order['total_price'], 2) ?></strong></p>

    <!-- Optionally, provide options to update order status here -->
    <h3>Update Order Status</h3>
    <form method="POST" action="manage_orders.php">
        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
        <label for="order_status">Order Status:</label>
        <select name="order_status" required>
            <option value="pending" <?= ($order['order_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
            <option value="shipped" <?= ($order['order_status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
            <option value="delivered" <?= ($order['order_status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
            <option value="canceled" <?= ($order['order_status'] === 'canceled') ? 'selected' : '' ?>>Canceled</option>
        </select><br>
        <button type="submit" name="update_order">Update Order Status</button>
    </form>

    <a href="manage_orders.php" class="btn">Back to Orders</a>
</section>
</main>
<?php include 'components/footer.php'; ?>
