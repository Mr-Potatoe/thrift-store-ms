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

// Fetch orders related to the seller, including the buyer's name
$query_orders = "SELECT o.*, oi.*, i.name AS item_name, u.first_name AS buyer_first_name, u.last_name AS buyer_last_name 
                 FROM orders o
                 JOIN order_items oi ON o.order_id = oi.order_id
                 JOIN items i ON oi.item_id = i.item_id
                 JOIN users u ON o.user_id = u.user_id
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
    <!-- Page Title -->
    <div class="container mt-5">
        <h1 class="text-primary mb-4">Manage Orders</h1>

        <!-- Orders Section -->
        <section class="dashboard-section">
            <h2>Orders</h2>
            <div class="row">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <!-- Order Header -->
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <strong>Order ID: <?= $order['order_id'] ?></strong>
                                <div class="order-status">
                                    <span class="badge <?= $order['order_status'] === 'completed' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= ucfirst($order['order_status']) ?>
                                    </span>
                                </div>
                            </div>

                            <!-- Order Details -->
                            <div class="card-body">
                                <p><strong>Item:</strong> <?= htmlspecialchars($order['item_name']) ?></p>
                                <p><strong>Quantity:</strong> <?= $order['quantity'] ?></p>
                                <p><strong>Total Price:</strong> PHP. <?= number_format($order['total_price'], 2) ?></p>
                                <p><strong>Buyer:</strong> <?= htmlspecialchars($order['buyer_first_name']) ?> <?= htmlspecialchars($order['buyer_last_name']) ?></p>
                                <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                                <p><strong>Payment Status:</strong> <?= ucfirst($order['payment_status']) ?></p>
                            </div>

                            <!-- View Order Details Button -->
                            <div class="card-footer text-end">
                                <a href="view_order.php?order_id=<?= $order['order_id'] ?>" class="btn btn-primary">View Order Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
</main>


<?php include 'components/footer.php'; ?>
