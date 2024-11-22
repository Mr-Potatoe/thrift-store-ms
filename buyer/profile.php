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
    if (isset($_POST['update_profile'])) {
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

    // Change password functionality
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Check if current password is correct
        if (password_verify($current_password, $user['password'])) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                // Validate new password (example: minimum 8 characters)
                if (strlen($new_password) >= 8) {
                    // Hash the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update the password in the database
                    $update_password_query = "UPDATE users SET password = ? WHERE user_id = ?";
                    $stmt_update_password = $pdo->prepare($update_password_query);
                    $stmt_update_password->execute([$hashed_password, $_SESSION['user_id']]);

                    echo "Password changed successfully!";
                    header("Location: profile.php");
                    exit;
                } else {
                    echo "New password must be at least 8 characters long.";
                }
            } else {
                echo "New password and confirmation do not match.";
            }
        } else {
            echo "Current password is incorrect.";
        }
    }
}
?>

<?php include 'components/header.php'; ?>

<section class="profile-section py-5">
    <div class="container">
        <h1 class="text-center mb-4">Your Profile</h1>

        <div class="text-center mb-4">
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile</a>
        </div>

        <!-- Profile Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
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
        </div>

        <!-- Change Password Section -->
        <h2 class="mt-5">Change Password</h2>
        <form method="POST" class="mt-4">
            <div class="form-group">
                <label for="current_password">Current Password:</label>
                <input type="password" name="current_password" id="current_password" required class="form-control">
            </div>

            <div class="form-group">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required class="form-control">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm New Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required class="form-control">
            </div>

            <button type="submit" name="change_password" class="btn btn-success mt-3">Change Password</button>
        </form>
    </div>
</section>


<?php include 'components/footer.php'; ?>