<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Calculate total price from cart by joining with items table
$total_price = 0;
$cart_query = "SELECT c.item_id, c.quantity, i.price FROM cart c
               JOIN items i ON c.item_id = i.item_id
               WHERE c.user_id = ?";
$stmt_cart = $pdo->prepare($cart_query);
$stmt_cart->execute([$_SESSION['user_id']]);
$cart_items = $stmt_cart->fetchAll();

// Check if cart is empty
if (empty($cart_items)) {
    // Redirect to the cart page with a message
    $_SESSION['message'] = "Your cart is empty. Please add items to your cart before checking out.";
    header("Location: cart.php");
    exit;
}

foreach ($cart_items as $cart_item) {
    $total_price += $cart_item['quantity'] * $cart_item['price'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get shipping address
    $shipping_address = $_POST['shipping_address'];

    // Insert order into orders table
    $query = "INSERT INTO orders (user_id, total_price, shipping_address) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$_SESSION['user_id'], $total_price, $shipping_address]);

    // Get the last inserted order ID
    $order_id = $pdo->lastInsertId();

    // Insert order items into order_items table
    foreach ($cart_items as $cart_item) {
        $order_item_query = "INSERT INTO order_items (order_id, item_id, quantity, price) VALUES (?, ?, ?, ?)";
        $stmt_order_item = $pdo->prepare($order_item_query);
        $stmt_order_item->execute([$order_id, $cart_item['item_id'], $cart_item['quantity'], $cart_item['price']]);
    }

    // Clear the cart
    $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
    $stmt_clear_cart = $pdo->prepare($clear_cart_query);
    $stmt_clear_cart->execute([$_SESSION['user_id']]);

    // Redirect to orders page
    header("Location: orders.php");
    exit;
}
?>

<?php include 'components/header.php'; ?>

<section class="checkout-section">
    <h1 class="checkout-title">Checkout</h1>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="message"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <form method="POST" action="checkout.php" class="checkout-form">
        <label for="shipping_address" class="checkout-label">Shipping Address:</label><br>
        <textarea name="shipping_address" class="checkout-textarea" required></textarea><br>

        <label for="total_price" class="checkout-label">Total Price:</label><br>
        <input type="text" name="total_price" class="checkout-input" required readonly value="<?= $total_price ?>"><br>

        <button type="submit" class="checkout-button">Place Order</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>
