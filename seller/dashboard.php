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

$query_items = "
    SELECT i.*, GROUP_CONCAT(im.image_url) AS image_urls
    FROM items i
    LEFT JOIN item_images im ON i.item_id = im.item_id
    WHERE i.shop_id = ?
    GROUP BY i.item_id
";
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

?>

<?php include 'components/header.php'; ?>

<main class="dashboard-content">
    <section class="welcome">
        <h2>Welcome to your Dashboard, <?= htmlspecialchars($shop['shop_name']) ?>!</h2>
        <p>View your items, orders, and manage your shop.</p>
    </section>

    <section class="dashboard-section">
        <h2>Your Items</h2>
        <div class="items-list">
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <div class="item-card">
                        <div id="carousel-<?= $item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php
                                $images = explode(',', $item['image_urls']);
                                foreach ($images as $index => $image): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="../uploads/items/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="d-block w-100">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $item['item_id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $item['item_id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>
                        <div class="item-info mt-3">
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


</main>


<?php include 'components/footer.php'; ?>