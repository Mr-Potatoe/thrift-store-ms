<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Get item ID from the query parameter
$item_id = $_GET['item_id'] ?? null;
if (!$item_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch item details from the database to ensure it belongs to the seller
$query_item = "SELECT * FROM items WHERE item_id = ? AND shop_id = ?";
$stmt_item = $pdo->prepare($query_item);
$stmt_item->execute([$item_id, $seller_id]);
$item = $stmt_item->fetch();

if ($item) {
    try {
        // Delete the item from the database
        $query_delete = "DELETE FROM items WHERE item_id = ?";
        $stmt_delete = $pdo->prepare($query_delete);
        $stmt_delete->execute([$item_id]);

        // Optionally, delete the item image from the server (if applicable)
        if ($item['image']) {
            unlink("../uploads/items/" . $item['image']);
        }

        // Redirect to the dashboard after successful deletion
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
} else {
    header("Location: dashboard.php");
    exit;
}
