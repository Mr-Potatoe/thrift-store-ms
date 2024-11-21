<?php
session_start();

// Include database connection
require_once 'config/database.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check if the username and password are correct
    $query = "SELECT * FROM users WHERE username = ? OR email = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$username, $username]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Store user details in session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect to dashboard based on role
        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } elseif ($user['role'] === 'seller') {
            header("Location: seller/dashboard.php");
        } elseif ($user['role'] === 'buyer') {
            header("Location: buyer/index.php");
        }
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/css/login.css">
</head>
<body>

    <div class="login-container">
        <div class="login-box">
            <h2>Login</h2>
            
            <form method="POST" action="login.php">
                <div class="input-group">
                    <label for="username">Username or Email</label>
                    <input type="text" name="username" id="username" required placeholder="Enter your username or email">
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" required placeholder="Enter your password">
                </div>

                <?php
                if (isset($error)) {
                    echo "<div class='error-message'>$error</div>";
                }
                ?>

                <button type="submit" name="login" class="btn">Login</button>
            </form>

            <p class="signup-link">Don't have an account? <a href="register.php">Sign up</a></p>
        </div>
    </div>

</body>
</html>
