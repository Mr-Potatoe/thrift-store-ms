<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert user into database
    $query = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$username, $email, $hashed_password, $role])) {
        echo "User created successfully!";
        header("Location: manage_users.php");
        exit;
    } else {
        echo "Error creating user.";
    }
}
?>

<?php include 'components/header.php'; ?>
<h1>Create New User</h1>
<form method="POST" action="create_user.php">
    <label for="username">Username:</label>
    <input type="text" name="username" required><br>
    <label for="email">Email:</label>
    <input type="email" name="email" required><br>
    <label for="password">Password:</label>
    <input type="password" name="password" required><br>
    <label for="role">Role:</label>
    <select name="role" required>
        <option value="admin">Admin</option>
        <option value="seller">Seller</option>
        <option value="buyer">Buyer</option>
    </select><br>
    <button type="submit">Create User</button>
</form>
<?php include 'components/footer.php'; ?>