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

// Fetch orders related to the seller
$query_orders = "SELECT o.*, oi.*, i.name FROM orders o
                 JOIN order_items oi ON o.order_id = oi.order_id
                 JOIN items i ON oi.item_id = i.item_id
                 WHERE i.shop_id = ?";
$stmt_orders = $pdo->prepare($query_orders);
$stmt_orders->execute([$shop['shop_id']]);
$orders = $stmt_orders->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $order_id = $_POST['order_id'];
    $order_status = $_POST['order_status'];

    try {
        // Start a transaction to ensure both order status and stock update are handled together
        $pdo->beginTransaction();

        // Update order status
        $query_update_order = "UPDATE orders SET order_status = ? WHERE order_id = ?";
        $stmt_update = $pdo->prepare($query_update_order);
        $stmt_update->execute([$order_status, $order_id]);

        // If the order is marked as 'delivered', reduce the quantity of the items
        if ($order_status === 'delivered') {
            // Update item quantities in the items table for each item in the order
            $query_items = "SELECT oi.item_id, oi.quantity FROM order_items oi WHERE oi.order_id = ?";
            $stmt_items = $pdo->prepare($query_items);
            $stmt_items->execute([$order_id]);
            $order_items = $stmt_items->fetchAll();

            foreach ($order_items as $order_item) {
                // Reduce the quantity in the items table
                $query_update_item = "UPDATE items SET quantity = quantity - ? WHERE item_id = ?";
                $stmt_update_item = $pdo->prepare($query_update_item);
                $stmt_update_item->execute([$order_item['quantity'], $order_item['item_id']]);
            }
        }

        // Commit the transaction
        $pdo->commit();

        // Redirect after successful update
        header("Location: manage_orders.php");
        exit;
    } catch (PDOException $e) {
        // Rollback the transaction in case of an error
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<?php include 'components/header.php'; ?>

<?php if (isset($error)) {
    echo "<p style='color: red;'>$error</p>";
} ?>

<main class="dashboard-content">
    <h1>Manage Orders</h1>
    <section class="dashboard-section">
        <h2>Orders</h2>
        <ul>
            <?php foreach ($orders as $order): ?>
                <li class="order-card">
                    <strong>Order ID: <?= $order['order_id'] ?></strong><br>
                    Item: <?= $order['name'] ?><br>
                    Quantity: <?= $order['quantity'] ?><br>
                    Total Price: $<?= number_format($order['total_price'], 2) ?><br>
                    Shipping Address: <?= htmlspecialchars($order['shipping_address']) ?><br>
                    Payment Status: <?= ucfirst($order['payment_status']) ?><br>
                    Order Status: <?= ucfirst($order['order_status']) ?><br>
                    
                    <!-- View Order Details Button -->
                    <a href="view_order.php?order_id=<?= $order['order_id'] ?>" class="btn">View Order Details</a>

                    <form method="POST" action="manage_orders.php">
                        <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                        <label for="order_status">Order Status:</label>
                        <select name="order_status" required>
                            <option value="pending" <?= ($order['order_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="shipped" <?= ($order['order_status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= ($order['order_status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                            <option value="canceled" <?= ($order['order_status'] === 'canceled') ? 'selected' : '' ?>>Canceled</option>
                        </select><br>
                        <button type="submit" class="btn" name="update_order">Update Order Status</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
</main>

<?php include 'components/footer.php'; ?>
