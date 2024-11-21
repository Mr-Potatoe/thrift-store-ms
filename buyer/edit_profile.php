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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Update profile information
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];

    $update_query = "UPDATE users SET username = ?, email = ?, phone = ?, address = ?, first_name = ?, last_name = ? WHERE user_id = ?";
    $stmt_update = $pdo->prepare($update_query);
    $stmt_update->execute([$username, $email, $phone, $address, $first_name, $last_name, $_SESSION['user_id']]);

    echo "Profile updated successfully!";
    header("Location: profile.php");
    exit;
}
?>

<?php include 'components/header.php'; ?>
<section class="profile-update-section">
    <h1 class="profile-update-title">Your Profile</h1>

    <form method="POST" action="profile.php" class="profile-update-form">
        <div class="form-group">
            <label for="username">Username:</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required class="form-input">
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-input">
        </div>

        <div class="form-group">
            <label for="phone">Phone:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required class="form-input">
        </div>

        <div class="form-group">
            <label for="address">Address:</label>
            <textarea name="address" required class="form-input"><?= htmlspecialchars($user['address']) ?></textarea>
        </div>

        <div class="form-group">
            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required class="form-input">
        </div>

        <div class="form-group">
            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required class="form-input">
        </div>

        <button type="submit" class="submit-button">Update Profile</button>
    </form>
</section>

<?php include 'components/footer.php'; ?>