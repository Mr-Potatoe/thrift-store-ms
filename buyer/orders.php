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
    // Add items back to the cart
    foreach ($order_items as $item) {
        // First, check if the item is already in the cart
        $check_cart_query = "SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?";
        $stmt_check_cart = $pdo->prepare($check_cart_query);
        $stmt_check_cart->execute([$_SESSION['user_id'], $item['item_id']]);
        $cart_item = $stmt_check_cart->fetch();

        if ($cart_item) {
            // If the item is already in the cart, update the quantity
            $update_cart_query = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?";
            $stmt_update_cart = $pdo->prepare($update_cart_query);
            $stmt_update_cart->execute([$item['quantity'], $_SESSION['user_id'], $item['item_id']]);
        } else {
            // If the item is not in the cart, insert it
            $add_to_cart_query = "INSERT INTO cart (user_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
            $stmt_add_to_cart = $pdo->prepare($add_to_cart_query);
            $stmt_add_to_cart->execute([$_SESSION['user_id'], $item['item_id'], $item['quantity'], $item['price']]);
        }
    }
}

// Check if reorder is requested
if (isset($_GET['reorder_order_id'])) {
    $order_id = $_GET['reorder_order_id'];

    // Get items from the canceled order
    $order_items_query = "SELECT oi.item_id, oi.quantity, i.price FROM order_items oi JOIN items i ON oi.item_id = i.item_id WHERE oi.order_id = ?";
    $stmt_order_items = $pdo->prepare($order_items_query);
    $stmt_order_items->execute([$order_id]);
    $order_items = $stmt_order_items->fetchAll();

    // Check if there are items to reorder
    if ($order_items) {
        // Add items back to the cart
        foreach ($order_items as $item) {
            // First, check if the item is already in the cart
            $check_cart_query = "SELECT quantity FROM cart WHERE user_id = ? AND item_id = ?";
            $stmt_check_cart = $pdo->prepare($check_cart_query);
            $stmt_check_cart->execute([$_SESSION['user_id'], $item['item_id']]);
            $cart_item = $stmt_check_cart->fetch();

            if ($cart_item) {
                // If the item is already in the cart, update the quantity
                $update_cart_query = "UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND item_id = ?";
                $stmt_update_cart = $pdo->prepare($update_cart_query);
                $stmt_update_cart->execute([$item['quantity'], $_SESSION['user_id'], $item['item_id']]);
            } else {
                // If the item is not in the cart, insert it
                $add_to_cart_query = "INSERT INTO cart (user_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt_add_to_cart = $pdo->prepare($add_to_cart_query);
                $stmt_add_to_cart->execute([$_SESSION['user_id'], $item['item_id'], $item['quantity'], $item['price']]);
            }
        }

        echo "Items added to your cart!";
        header("Location: cart.php");
        exit;
    } else {
        echo "No items found in this order to reorder.";
    }
}

// Get orders for the logged-in user along with feedback and seller's response
$query = "
   SELECT 
    o.*, 
    f.feedback_id, 
    f.rating, 
    f.comment AS feedback_comment, 
    f.response AS seller_response, 
    f.created_at AS feedback_date,
    GROUP_CONCAT(DISTINCT s.shop_name SEPARATOR ', ') AS shop_names
FROM orders o
LEFT JOIN feedback f ON o.order_id = f.order_id
LEFT JOIN order_items oi ON o.order_id = oi.order_id
LEFT JOIN items i ON oi.item_id = i.item_id
LEFT JOIN shops s ON i.shop_id = s.shop_id
WHERE o.user_id = ?
GROUP BY o.order_id
ORDER BY o.order_date DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();
?>

<?php include 'components/header.php'; ?>
<section class="orders-page py-5">
    <div class="container">
        <h1 class="text-center mb-4">Your Orders</h1>

        <?php if (empty($orders)): ?>
            <p class="text-center">You have no orders yet.</p>
        <?php else: ?>
            <div class="list-group">
                <!-- Loop through orders -->
                <?php foreach ($orders as $order): ?>
                    <div class="list-group-item mb-3">
                        <h4>Order ID: <?= $order['order_id'] ?> - Total: PHP <?= number_format($order['total_price'], 2) ?></h4>
                        <p><strong>Status:</strong> <?= ucfirst($order['order_status']) ?></p>
                        <p><strong>Shipping Address:</strong> <?= $order['shipping_address'] ?></p>
                        <p><strong>Order Date:</strong> <?= $order['order_date'] ?></p>
                        <p><strong>Shop:</strong> <?= htmlspecialchars($order['shop_names']) ?></p>

                        <h5>Items:</h5>
                        <ul class="list-unstyled">
                            <?php
                            $order_items_query = "SELECT i.name, oi.quantity, oi.price FROM order_items oi JOIN items i ON oi.item_id = i.item_id WHERE oi.order_id = ?";
                            $stmt_order_items = $pdo->prepare($order_items_query);
                            $stmt_order_items->execute([$order['order_id']]);
                            $order_items = $stmt_order_items->fetchAll();

                            foreach ($order_items as $item):
                            ?>
                                <li><?= $item['name'] ?> - PHP <?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?></li>
                            <?php endforeach; ?>
                        </ul>

                        <!-- Reorder button for canceled orders -->
                        <?php if ($order['order_status'] == 'canceled'): ?>
                            <a href="?reorder_order_id=<?= $order['order_id'] ?>" class="btn btn-warning">Reorder Items</a>
                        <?php endif; ?>

                        <!-- Feedback and seller's response -->
                        <?php if ($order['feedback_id']): ?>
                            <div class="feedback-section mt-3">
                                <h6>Feedback:</h6>
                                <p><strong>Rating:</strong> <?= $order['rating'] ?>/5</p>
                                <p><strong>Comment:</strong> <?= htmlspecialchars($order['feedback_comment']) ?></p>
                                <p><strong>Feedback Date:</strong> <?= $order['feedback_date'] ?></p>

                                <?php if ($order['seller_response']): ?>
                                    <h6>Seller's Response:</h6>
                                    <p><?= htmlspecialchars($order['seller_response']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <!-- Feedback Button if no feedback is left yet -->
                            <a href="feedback.php?order_id=<?= $order['order_id'] ?>" class="btn btn-secondary mt-2">Leave Feedback</a>
                        <?php endif; ?>

                        <!-- Cancel order button with modal trigger -->
                        <?php if ($order['order_status'] == 'pending' || $order['order_status'] == 'shipped'): ?>
                            <button class="btn btn-danger mt-2" data-bs-toggle="modal" data-bs-target="#cancelOrderModal<?= $order['order_id'] ?>">
                                Cancel Order
                            </button>
                        <?php endif; ?>
                    </div>

                    <!-- Modal for Canceling Order -->
                    <div class="modal fade" id="cancelOrderModal<?= $order['order_id'] ?>" tabindex="-1" aria-labelledby="cancelOrderModalLabel<?= $order['order_id'] ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="cancelOrderModalLabel<?= $order['order_id'] ?>">Confirm Cancellation</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to cancel this order? Once canceled, you will not be able to reverse this action.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <a href="?cancel_order_id=<?= $order['order_id'] ?>" class="btn btn-danger">Yes, Cancel Order</a>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>


<?php include 'components/footer.php'; ?>