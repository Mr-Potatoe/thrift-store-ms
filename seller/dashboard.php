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
    <div class="container mt-5">
        <!-- Dashboard Welcome Message -->
        <div class="row">
            <div class="col-12 text-center">
                <h3>Welcome to your Dashboard, <?= htmlspecialchars($shop['shop_name']) ?>!</h3>
            </div>
        </div>

        <!-- Dashboard Sections -->
        <div class="row mt-4">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        Manage Items
                    </div>
                    <div class="card-body">
                        <p>Quickly manage your shop items and update inventory.</p>
                        <a href="manage_items.php" class="btn btn-primary w-100">Go to Manage Items</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        Manage Orders
                    </div>
                    <div class="card-body">
                        <p>View and process customer orders efficiently.</p>
                        <a href="manage_orders.php" class="btn btn-success w-100">Go to Manage Orders</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-header bg-warning text-white">
                        Manage Feedback
                    </div>
                    <div class="card-body">
                        <p>Review feedback and improve your customer service.</p>
                        <a href="manage_feedback.php" class="btn btn-warning w-100">Go to Manage Feedback</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section: Your Items -->
    <section class="dashboard-section mt-5">
        <div class="container">
            <h2>Your Items</h2>
            <div class="row">
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <!-- Carousel for Item Images -->
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
                                <!-- Item Information -->
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="card-text">Price: PHP. <?= number_format($item['price'], 2) ?></p>
                                    <p class="card-text">Quantity: <?= $item['quantity'] ?></p>
                                    <p class="card-text">Condition: <?= ucfirst($item['condition']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p>No items listed yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Section: Your Orders -->
    <section class="dashboard-section mt-5">
        <div class="container">
            <h2>Your Orders</h2>
            <div class="row">
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title">Buyer: <?= htmlspecialchars($order['buyer_name']) ?></h5>
                                    <p class="card-text">Item: <?= htmlspecialchars($order['name']) ?></p>
                                    <p class="card-text">Quantity: <?= $order['quantity'] ?></p>
                                    <p class="card-text">Status: <?= htmlspecialchars($order['order_status']) ?></p>
                                    <a href="view_order.php?order_id=<?= $order['order_id'] ?>" class="btn btn-info w-100">View Order Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <p>No orders yet.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

</main>

<?php include 'components/footer.php'; ?>