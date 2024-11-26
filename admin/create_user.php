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
<div class="container my-5">
    <h1 class="text-center mb-4">Create New User</h1>
    <div class="card shadow">
        <div class="card-body">
            <form method="POST" action="create_user.php">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Enter username" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter email address" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" id="role" class="form-select" required>
                        <option value="" disabled selected>Select a role</option>
                        <option value="admin">Admin</option>
                        <option value="seller">Seller</option>
                        <option value="buyer">Buyer</option>
                    </select>
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary">Create User</button>
                    <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
<?php include 'components/footer.php'; ?>