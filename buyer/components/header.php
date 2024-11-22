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

// Get the number of pending orders for the user
$query_pending_order_count = "SELECT COUNT(*) AS pending_order_count FROM orders WHERE user_id = ? AND order_status = 'pending'";
$stmt_pending_order_count = $pdo->prepare($query_pending_order_count);
$stmt_pending_order_count->execute([$_SESSION['user_id']]);
$pending_order_count = $stmt_pending_order_count->fetch();

// Default to 0 if no pending orders
$pending_order_count = $pending_order_count['pending_order_count'] ?? 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thrifted Outlet</title>
    <link rel="stylesheet" href="css/styles.css"> <!-- Link to your CSS file -->
    <!-- Include Bootstrap JS and CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a href="dashboard.php" class="navbar-brand">Thrifted Outlet</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="categories.php" class="nav-link">Categories</a>
                    </li>
                    <li class="nav-item">
                        <a href="shops.php" class="nav-link">Shops</a>
                    </li>
                    <li class="nav-item">
                        <a href="cart.php" class="nav-link">Cart (<?= $item_count ?>)</a>
                    </li>
                    <li class="nav-item">
                        <a href="orders.php" class="nav-link">Orders (<?= $pending_order_count ?>)</a> <!-- Display Pending Orders Count -->
                    </li>
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">Profile</a>
                    </li>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#logoutModal">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">Login</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Modal for Logout Confirmation -->
    <div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="logoutModalLabel">Confirm Logout</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to logout?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="../logout.php" class="btn btn-primary">Logout</a>
                </div>
            </div>
        </div>
    </div>