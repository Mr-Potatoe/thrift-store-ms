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

<section class="profile-section">
    <h1 class="profile-title">Your Profile</h1>

    <a href="edit_profile.php" class="edit-profile-link">Edit Profile</a>

    <table class="profile-table">
        <tr>
            <td class="profile-table-label"><strong>Username:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['username']) ?></td>
        </tr>
        <tr>
            <td class="profile-table-label"><strong>Email:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['email']) ?></td>
        </tr>
        <tr>
            <td class="profile-table-label"><strong>Phone:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['phone']) ?></td>
        </tr>
        <tr>
            <td class="profile-table-label"><strong>Address:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['address']) ?></td>
        </tr>
        <tr>
            <td class="profile-table-label"><strong>First Name:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['first_name']) ?></td>
        </tr>
        <tr>
            <td class="profile-table-label"><strong>Last Name:</strong></td>
            <td class="profile-table-value"><?= htmlspecialchars($user['last_name']) ?></td>
        </tr>
    </table>
</section>

<?php include 'components/footer.php'; ?>