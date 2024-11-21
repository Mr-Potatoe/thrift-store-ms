<?php
// Include database connection
require_once '../config/database.php';

// Get the number of items in the user's cart
$query_cart_count = "SELECT COUNT(*) AS item_count FROM cart WHERE user_id = ?";
$stmt_cart_count = $pdo->prepare($query_cart_count);
$stmt_cart_count->execute([$_SESSION['user_id']]);
$cart_count = $stmt_cart_count->fetch();

// Default to 0 if no items in cart
$item_count = $cart_count['item_count'] ?? 0;
?>

<!-- header.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thrifted Outlet</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to your CSS file -->
</head>
<body>
    <!-- Navbar -->
    <nav>
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="categories.php">Categories</a></li>
            <li><a href="cart.php">Cart (<?= $item_count ?>)</a></li> <!-- Cart with item count -->
            <!-- <li><a href="checkout.php">Checkout</a></li> -->
            <li><a href="orders.php">Orders</a></li>
            <li><a href="profile.php">Profile</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a href="../logout.php">Logout</a></li>
            <?php else: ?>
                <li><a href="login.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </nav>
