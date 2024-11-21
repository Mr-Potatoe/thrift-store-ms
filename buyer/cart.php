<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Process adding item to cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get item ID and quantity from the form
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // Check if the item is already in the cart
    $query_check = "SELECT * FROM cart WHERE user_id = ? AND item_id = ?";
    $stmt_check = $pdo->prepare($query_check);
    $stmt_check->execute([$user_id, $item_id]);
    $existing_item = $stmt_check->fetch();

    if ($existing_item) {
        // If the item is already in the cart, update the quantity
        $new_quantity = $existing_item['quantity'] + $quantity;
        $query_update = "UPDATE cart SET quantity = ? WHERE user_id = ? AND item_id = ?";
        $stmt_update = $pdo->prepare($query_update);
        $stmt_update->execute([$new_quantity, $user_id, $item_id]);
    } else {
        // If the item is not in the cart, insert it
        $query_insert = "INSERT INTO cart (user_id, item_id, quantity) VALUES (?, ?, ?)";
        $stmt_insert = $pdo->prepare($query_insert);
        $stmt_insert->execute([$user_id, $item_id, $quantity]);
    }

    // Redirect to the cart page to show updated cart
    header("Location: cart.php");
    exit;
}

// View cart items
$query = "SELECT ci.cart_id, i.name, ci.quantity, i.price FROM cart ci JOIN items i ON ci.item_id = i.item_id WHERE ci.user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$cart_items = $stmt->fetchAll();

// Calculate total price
$total_price = 0;
foreach ($cart_items as $cart_item) {
    $total_price += $cart_item['quantity'] * $cart_item['price'];
}
?>

<?php include 'components/header.php'; ?>
<section class="cart-section">
    <h1 class="cart-title">Your Cart</h1>

    <!-- Cart Items -->
    <ul class="cart-items-list">
        <?php foreach ($cart_items as $cart_item): ?>
            <li class="cart-item">
                <span class="cart-item-name"><?= $cart_item['name'] ?></span> 
                - 
                <span class="cart-item-price">$<?= number_format($cart_item['price'], 2) ?></span> 
                x 
                <span class="cart-item-quantity"><?= $cart_item['quantity'] ?></span>
            </li>
        <?php endforeach; ?>
    </ul>

    <!-- Total Price -->
    <h3 class="cart-total">Total: $<?= number_format($total_price, 2) ?></h3>

    <a href="checkout.php" class="checkout-button">Proceed to Checkout</a>
</section>

<?php include 'components/footer.php'; ?>
