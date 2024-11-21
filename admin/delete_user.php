<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Delete the user from the database
    $query = "DELETE FROM users WHERE user_id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$user_id])) {
        echo "User deleted successfully!";
        header("Location: manage_users.php");
        exit;
    } else {
        echo "Error deleting user.";
    }
}
?>
