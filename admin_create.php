<?php
// Include database connection
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert the admin user into the database
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'admin')";
    $stmt = $pdo->prepare($query);
    
    if ($stmt->execute([$username, $email, $hashed_password])) {
        echo "Admin user created successfully!";
    } else {
        echo "Error creating admin user.";
    }
}
?>

<!-- Admin Creation Form -->
<form method="POST" action="admin_create.php">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>

    <label for="email">Email:</label>
    <input type="email" name="email" required><br>

    <label for="password">Password:</label>
    <input type="password" name="password" required><br>

    <button type="submit">Create Admin</button>
</form>
