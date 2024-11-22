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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    // Validate and sanitize inputs
    $item_id = filter_input(INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT);
    $quantity = filter_input(INPUT_POST, 'quantity', FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($item_id && $quantity > 0) {
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
    }

    // Redirect to the cart page
    header("Location: cart.php");
    exit;
}

// Process cancel order
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    $cart_id = $_POST['cart_id'];
    $query_delete = "DELETE FROM cart WHERE cart_id = ?";
    $stmt_delete = $pdo->prepare($query_delete);
    $stmt_delete->execute([$cart_id]);

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
<section class="cart-section container py-5">
    <h1 class="cart-title text-center mb-4">Your Cart</h1>

    <!-- Cart Items -->
    <div class="cart-items-list">
        <?php foreach ($cart_items as $cart_item): ?>
            <div class="card mb-3">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title"><?= htmlspecialchars($cart_item['name']) ?></h5>
                        <p class="card-text">
                            Price: PHP <?= number_format($cart_item['price'], 2) ?><br>
                            Quantity: <?= $cart_item['quantity'] ?>
                        </p>
                    </div>
                    <div class="d-flex flex-column align-items-end">
                        <!-- Cancel Order Button -->
                        <form method="POST" action="cart.php" style="display:inline;">
                            <input type="hidden" name="action" value="cancel">
                            <input type="hidden" name="cart_id" value="<?= $cart_item['cart_id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Total Price -->
    <div class="total-price text-end mb-4">
        <h3>Total: PHP <?= number_format($total_price, 2) ?></h3>
    </div>

    <!-- Checkout Button -->
    <div class="text-center">
        <a href="checkout.php" class="btn btn-success btn-lg">Proceed to Checkout</a>
    </div>
</section>
<?php include 'components/footer.php'; ?>