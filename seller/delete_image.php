<?php
session_start();
require_once '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$image_id = $_GET['image_id'] ?? null;
$item_id = $_GET['item_id'] ?? null;

if ($image_id && $item_id) {
    // Fetch the image data
    $query_image = "SELECT * FROM item_images WHERE image_id = ? AND item_id = ?";
    $stmt_image = $pdo->prepare($query_image);
    $stmt_image->execute([$image_id, $item_id]);
    $image = $stmt_image->fetch();

    if ($image) {
        // Delete the image file from the server
        $file_path = "../uploads/items/" . $image['image_url'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }

        // Delete the image record from the database
        $query_delete_image = "DELETE FROM item_images WHERE image_id = ?";
        $stmt_delete_image = $pdo->prepare($query_delete_image);
        $stmt_delete_image->execute([$image_id]);
    }
}

header("Location: edit_item.php?item_id=" . $item_id);
exit;
