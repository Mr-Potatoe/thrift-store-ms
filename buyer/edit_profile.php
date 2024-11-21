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
    <h1>Your Profile</h1>

    <form method="POST" action="profile.php">
        <label for="username">Username:</label><br>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>

        <label for="email">Email:</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>

        <label for="phone">Phone:</label><br>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>" required><br>

        <label for="address">Address:</label><br>
        <textarea name="address" required><?= htmlspecialchars($user['address']) ?></textarea><br>

        <label for="first_name">First Name:</label><br>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required><br>

        <label for="last_name">Last Name:</label><br>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required><br>

        <button type="submit">Update Profile</button>
    </form>
    <?php include 'components/footer.php'; ?>
