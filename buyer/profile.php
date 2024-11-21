<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

// Get user details
$query = "SELECT * FROM users WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

?>

<?php include 'components/header.php'; ?>

<h1>Your Profile</h1>

<a href="edit_profile.php">Edit Profile</a>

<table>
    <tr>
        <td><strong>Username:</strong></td>
        <td><?= htmlspecialchars($user['username']) ?></td>
    </tr>
    <tr>
        <td><strong>Email:</strong></td>
        <td><?= htmlspecialchars($user['email']) ?></td>
    </tr>
    <tr>
        <td><strong>Phone:</strong></td>
        <td><?= htmlspecialchars($user['phone']) ?></td>
    </tr>
    <tr>
        <td><strong>Address:</strong></td>
        <td><?= htmlspecialchars($user['address']) ?></td>
    </tr>
    <tr>
        <td><strong>First Name:</strong></td>
        <td><?= htmlspecialchars($user['first_name']) ?></td>
    </tr>
    <tr>
        <td><strong>Last Name:</strong></td>
        <td><?= htmlspecialchars($user['last_name']) ?></td>
    </tr>
</table>

<?php include 'components/footer.php'; ?>
