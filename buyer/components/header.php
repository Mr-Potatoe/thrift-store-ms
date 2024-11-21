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

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thrifted Outlet</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to your CSS file -->
    <!-- Include Bootstrap JS and CSS -->
<!-- Add the following to your HTML file if you haven't already -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="logo">Thrifted Outlet</a>
            <ul class="navbar-links">
                <li><a href="dashboard.php" class="nav-link">Home</a></li>
                <li><a href="categories.php" class="nav-link">Categories</a></li>
                <li><a href="cart.php" class="nav-link">Cart (<?= $item_count ?>)</a></li>
                <li><a href="orders.php" class="nav-link">Orders</a></li>
                <li><a href="profile.php" class="nav-link">Profile</a></li>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="../logout.php" class="nav-link">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php" class="nav-link">Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </nav>
