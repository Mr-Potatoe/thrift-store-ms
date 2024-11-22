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
    $category_name = $_POST['name'];

    // Insert category into the database
    $query = "INSERT INTO categories (category_name) VALUES (?)";  // Updated column name
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$category_name])) {
        echo "Category created successfully!";
        header("Location: manage_categories.php");
        exit;
    } else {
        echo "Error creating category.";
    }
}
?>

<?php include 'components/header.php'; ?>
    <h1>Create New Category</h1>
    <form method="POST" action="create_category.php">
        <label for="name">Category Name:</label>
        <input type="text" name="name" required><br>
        <button type="submit">Create Category</button>
    </form>
    <?php include 'components/footer.php'; ?>
