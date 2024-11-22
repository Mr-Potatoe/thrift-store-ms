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
<section class="profile-update-section py-5">
    <div class="container">
        <h1 class="text-center mb-4">Update Your Profile</h1>

        <!-- Profile Update Form -->
        <form method="POST" action="profile.php" class="profile-update-form">
            <div class="form-group mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label for="phone" class="form-label">Phone:</label>
                <input type="text" name="phone" id="phone" value="<?= htmlspecialchars($user['phone']) ?>" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea name="address" id="address" required class="form-control"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>

            <div class="form-group mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" name="first_name" id="first_name" value="<?= htmlspecialchars($user['first_name']) ?>" required class="form-control">
            </div>

            <div class="form-group mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" name="last_name" id="last_name" value="<?= htmlspecialchars($user['last_name']) ?>" required class="form-control">
            </div>

            <button type="submit" class="btn btn-success w-100">Update Profile</button>
        </form>
    </div>
</section>

<?php include 'components/footer.php'; ?>