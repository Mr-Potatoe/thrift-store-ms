<?php
// Include database connection
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];
    $role = $_POST['role']; // 'buyer' or 'seller'
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the database
        try {
            $query = "INSERT INTO users (username, password, email, role, first_name, last_name, phone) 
                      VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$username, $hashed_password, $email, $role, $first_name, $last_name, $phone]);

            // Check if the user is a seller, and create a shop entry for the seller
            if ($role === 'seller') {
                $user_id = $pdo->lastInsertId();
                $shop_name = $_POST['shop_name'];
                $shop_description = $_POST['shop_description'];

                // Insert shop details for seller
                $shop_query = "INSERT INTO shops (user_id, shop_name, shop_description) 
                               VALUES (?, ?, ?)";
                $shop_stmt = $pdo->prepare($shop_query);
                $shop_stmt->execute([$user_id, $shop_name, $shop_description]);
            }

            // Redirect to login page after successful registration
            header("Location: login.php");
            exit;
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/register.css">
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h1>Create an Account</h1>

            <!-- Display error messages -->
            <?php if (isset($error)) { ?>
                <div class="error-message"><?= $error; ?></div>
            <?php } ?>

            <form method="POST" action="register.php">
                <div class="form-grid">
                    <div class="input-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" placeholder="Enter your username" required>
                    </div>

                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                    </div>

                    <div class="input-group">
                        <label for="confirm_password">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                    </div>

                    <div class="input-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name" placeholder="Enter your first name" required>
                    </div>

                    <div class="input-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name" placeholder="Enter your last name" required>
                    </div>

                    <div class="input-group">
                        <label for="phone">Phone</label>
                        <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
                    </div>

                    <div class="input-group">
                        <label for="role">Select Role</label>
                        <select id="role" name="role" required>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                        </select>
                    </div>
                </div>

                <!-- Seller-specific fields -->
                <div id="seller_fields" class="seller-fields">
                    <div class="form-grid">
                        <div class="input-group">
                            <label for="shop_name">Shop Name</label>
                            <input type="text" id="shop_name" name="shop_name" placeholder="Enter your shop name">
                        </div>

                        <div class="input-group">
                            <label for="shop_description">Shop Description</label>
                            <textarea id="shop_description" name="shop_description" placeholder="Describe your shop"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn">Register</button>
            </form>
            <p>Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>

    <script>
        // Show seller-specific fields based on role
        document.querySelector('#role').addEventListener('change', function () {
            const sellerFields = document.getElementById('seller_fields');
            sellerFields.style.display = this.value === 'seller' ? 'block' : 'none';
        });
    </script>
</body>
</html>
