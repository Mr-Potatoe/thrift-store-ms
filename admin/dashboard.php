 
<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

?>

<?php include 'components/header.php'; ?>


    <h1>Welcome to the Admin Dashboard</h1>

    

<?php include 'components/footer.php'; ?>