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

<!-- Checkout Section -->
<section class="checkout-section py-5">
    <div class="container">
        <h1 class="checkout-title text-center mb-4">Checkout</h1>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info"><?= $_SESSION['message'];
                                            unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <form method="POST" action="checkout.php" class="checkout-form">
            <!-- Shipping Address -->
            <div class="mb-4">
                <label for="shipping_address" class="form-label">Shipping Address:</label>
                <textarea name="shipping_address" id="shipping_address" class="form-control" required></textarea>
            </div>

            <!-- Total Price -->
            <div class="mb-4">
                <label for="total_price" class="form-label">Total Price:</label>
                <input type="text" name="total_price" id="total_price" class="form-control" required readonly value="<?= $total_price ?>">
            </div>

            <!-- Place Order Button -->
            <div class="d-flex justify-content-center">
                <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
            </div>
        </form>
    </div>
</section>


<?php include 'components/footer.php'; ?>