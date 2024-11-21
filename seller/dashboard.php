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

// Fetch items listed by the seller, including image
$query_items = "SELECT * FROM items WHERE shop_id = ?";
$stmt_items = $pdo->prepare($query_items);
$stmt_items->execute([$shop['shop_id']]);
$items = $stmt_items->fetchAll();

// Fetch orders related to the seller, including the buyer's name
$query_orders = "SELECT o.*, oi.*, i.name, u.username AS buyer_name FROM orders o
                 JOIN order_items oi ON o.order_id = oi.order_id
                 JOIN items i ON oi.item_id = i.item_id
                 JOIN users u ON o.user_id = u.user_id
                 WHERE i.shop_id = ?";
$stmt_orders = $pdo->prepare($query_orders);
$stmt_orders->execute([$shop['shop_id']]);
$orders = $stmt_orders->fetchAll();

// Fetch disputes for this seller
$query_disputes = "SELECT * FROM disputes d
                   JOIN orders o ON d.order_id = o.order_id
                   WHERE o.user_id = ? AND d.status = 'open'";
$stmt_disputes = $pdo->prepare($query_disputes);
$stmt_disputes->execute([$seller_id]);
$disputes = $stmt_disputes->fetchAll();
?>

<?php include 'components/header.php'; ?>

<main class="dashboard-content">
        <section class="welcome">
            <h2>Welcome to your Dashboard, <?= htmlspecialchars($shop['shop_name']) ?>!</h2>
            <p>Manage your items, orders, and disputes all in one place.</p>
        </section>

        <section class="dashboard-section">
            <h2>Your Items</h2>
            <div class="items-list">
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="item-card">
                            <?php if ($item['image']): ?>
                                <img src="../uploads/items/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                            <?php else: ?>
                                <p>No image available</p>
                            <?php endif; ?>
                            <div class="item-info">
                                <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                                Price: $<?= number_format($item['price'], 2) ?><br>
                                Quantity: <?= $item['quantity'] ?><br>
                                Condition: <?= ucfirst($item['condition']) ?><br>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No items listed yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section">
            <h2>Your Orders</h2>
            <div class="orders-list">
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="order-card">
                            <strong>Buyer: <?= htmlspecialchars($order['buyer_name']) ?></strong><br>
                            Item: <?= htmlspecialchars($order['name']) ?><br>
                            Quantity: <?= $order['quantity'] ?><br>
                            Status: <?= htmlspecialchars($order['order_status']) ?><br>
                            <a href="view_order.php?order_id=<?= $order['order_id'] ?>" class="btn">View Order Details</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No orders yet.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="dashboard-section">
            <h2>Open Disputes</h2>
            <div class="disputes-list">
                <?php if ($disputes): ?>
                    <?php foreach ($disputes as $dispute): ?>
                        <div class="dispute-card">
                            <strong>Dispute ID: <?= $dispute['dispute_id'] ?></strong><br>
                            Order ID: <?= $dispute['order_id'] ?><br>
                            Issue: <?= htmlspecialchars($dispute['issue']) ?><br>
                            Status: <?= htmlspecialchars($dispute['status']) ?><br>
                            <a href="view_dispute.php?dispute_id=<?= $dispute['dispute_id'] ?>" class="btn">View Dispute</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No open disputes.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>


<?php include 'components/footer.php'; ?>
