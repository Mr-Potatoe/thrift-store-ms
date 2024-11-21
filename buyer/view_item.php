<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get item ID
$item_id = $_GET['item_id'];

// Get item details
$query = "SELECT * FROM items WHERE item_id = :item_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

// Get item images
$query_images = "SELECT * FROM item_images WHERE item_id = :item_id";
$stmt_images = $pdo->prepare($query_images);
$stmt_images->execute([':item_id' => $item_id]);
$images = $stmt_images->fetchAll();

// Get the number of items in the user's cart
$query_cart_count = "SELECT COUNT(*) AS item_count FROM cart WHERE user_id = ?";
$stmt_cart_count = $pdo->prepare($query_cart_count);
$stmt_cart_count->execute([$_SESSION['user_id']]);
$cart_count = $stmt_cart_count->fetch();

// Default to 0 if no items in cart
$item_count = $cart_count['item_count'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($item['name']) ?></title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to your CSS file -->
</head>

<body>
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="cart.php">Cart (<?= $item_count ?>)</a></li> <!-- Cart with item count -->
            <li><a href="orders.php">Orders</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <h1><?= htmlspecialchars($item['name']) ?></h1>

    <p><?= htmlspecialchars($item['description']) ?></p>
    <p>Condition: <?= ucfirst($item['condition']) ?></p>
    <p>Price: $<?= number_format($item['price'], 2) ?></p>

    <h2>Item Images</h2>
    <div>
        <?php foreach ($images as $image): ?>
            <img src="<?= htmlspecialchars($image['image_url']) ?>" alt="Item Image" width="200">
        <?php endforeach; ?>
    </div>

    <p><strong>Available Stock: <?= $item['quantity'] ?></strong></p>

    <form action="cart.php" method="POST">
        <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
        <label for="quantity">Quantity:</label>
        <input type="number" id="quantity" name="quantity" min="1" max="<?= $item['quantity'] ?>" value="1" required>
        <button type="submit">Add to Cart</button>
    </form>

    <?php include 'components/footer.php'; ?>
</body>

</html>
