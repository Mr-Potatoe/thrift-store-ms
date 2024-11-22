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
$query_order_details = "
    SELECT 
        o.*, 
        oi.*, 
        i.name AS item_name, 
        i.price AS item_price, 
        u.username AS buyer_name, 
        im.image_url AS item_image
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN items i ON oi.item_id = i.item_id
    JOIN users u ON o.user_id = u.user_id
    LEFT JOIN item_images im ON i.item_id = im.item_id
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
    <div class="container mt-5">
        <!-- Order Header -->
        <h1 class="order-header text-primary mb-4">Order Details for Buyer: <?= htmlspecialchars($order['buyer_name']) ?></h1>

        <!-- Order Information Section -->
        <section class="order-info card mb-4">
            <div class="card-body">
                <h2 class="section-title">Order Information</h2>
                <p><strong>Order Status:</strong> <span class="badge <?= strtolower($order['order_status']) ?>"><?= htmlspecialchars($order['order_status']) ?></span></p>
                <p><strong>Shipping Address:</strong> <?= htmlspecialchars($order['shipping_address']) ?></p>
                <p><strong>Payment Status:</strong> <span class="badge <?= strtolower($order['payment_status']) ?>"><?= htmlspecialchars($order['payment_status']) ?></span></p>
            </div>
        </section>

    <div class="row">
        <!-- Order Items Section -->
        <h3 class="section-title">Items in this Order</h3>
        <ul class="list-group">
            <?php
            $current_item_id = null;
            foreach ($order_details as $order_item):
                if ($current_item_id !== $order_item['item_id']):
                    if ($current_item_id !== null) echo '</div></li>'; // Close the previous item block
                    $current_item_id = $order_item['item_id']; ?>
                    <li class="list-group-item card mb-4">
                        <div class="order-item-header d-flex justify-content-between align-items-center">
                            <strong class="text-truncate" style="max-width: 60%"><?= htmlspecialchars($order_item['item_name']) ?></strong>
                            <span class="item-price text-muted">$<?= number_format($order_item['item_price'], 2) ?></span>
                        </div>
                        <div class="order-item-details">
                            <p><strong>Quantity:</strong> <?= $order_item['quantity'] ?></p>
                            <p><strong>Total:</strong> PHP.<?= number_format($order_item['item_price'] * $order_item['quantity'], 2) ?></p>
                        </div>

                        <!-- Bootstrap Carousel for Item Images -->
                        <div id="carousel<?= $order_item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php
                                // Fetch and display all images for this item
                                $images_query = "SELECT image_url FROM item_images WHERE item_id = ?";
                                $stmt_images = $pdo->prepare($images_query);
                                $stmt_images->execute([$order_item['item_id']]);
                                $images = $stmt_images->fetchAll();

                                foreach ($images as $index => $image):
                                ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="../uploads/items/<?= htmlspecialchars($image['image_url']) ?>"
                                            alt="<?= htmlspecialchars($order_item['item_name']) ?>"
                                            class="d-block w-100">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel<?= $order_item['item_id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel<?= $order_item['item_id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            </button>
                        </div>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>

        <!-- Total Order Price Section -->
        <div class="total-price card mb-4">
            <div class="card-body">
                <h3 class="section-title">Total Order Price</h3>
                <p><strong>Total Amount: PHP. <?= number_format($order['total_price'], 2) ?></strong></p>
            </div>
        </div>

        <!-- Update Order Status Form Section -->
        <div class="update-status card mb-4">
            <div class="card-body">
                <h3 class="section-title">Update Order Status</h3>
                <form method="POST" action="manage_orders.php">
                    <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
                    <div class="mb-3">
                        <label for="order_status" class="form-label">Order Status:</label>
                        <select name="order_status" required class="form-select">
                            <option value="pending" <?= ($order['order_status'] === 'pending') ? 'selected' : '' ?>>Pending</option>
                            <option value="shipped" <?= ($order['order_status'] === 'shipped') ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= ($order['order_status'] === 'delivered') ? 'selected' : '' ?>>Delivered</option>
                            <option value="canceled" <?= ($order['order_status'] === 'canceled') ? 'selected' : '' ?>>Canceled</option>
                        </select>
                    </div>
                    <button type="submit" name="update_order" class="btn btn-primary">Update Order Status</button>
                </form>
            </div>
        </div>

        <!-- Back Button -->
        <div class="text-center">
            <a href="manage_orders.php" class="btn btn-secondary">Back to Orders</a>
        </div>
        </div>
    </div>
</main>



<?php include 'components/footer.php'; ?>