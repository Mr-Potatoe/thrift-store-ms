<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Add category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $category_name = $_POST['category_name'];
    $query = "INSERT INTO categories (category_name) VALUES (:category_name)";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['category_name' => $category_name]);
}

// Edit category
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];
    $query = "UPDATE categories SET category_name = :category_name WHERE category_id = :category_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['category_name' => $category_name, 'category_id' => $category_id]);
}

// Delete category
if (isset($_GET['delete'])) {
    $category_id = $_GET['delete'];
    $query = "DELETE FROM categories WHERE category_id = :category_id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['category_id' => $category_id]);
}

// Fetch categories
$query = "SELECT * FROM categories";
$stmt = $pdo->query($query);
?>
<?php include 'components/header.php'; ?>
<h1>Manage Categories</h1>

<!-- Add category form -->
<form method="POST">
    <input type="text" name="category_name" placeholder="Category Name" required>
    <button type="submit" name="add_category">Add Category</button>
</form>

<!-- Categories list -->
<table>
    <thead>
        <tr>
            <th>Category Name</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($category = $stmt->fetch()): ?>
        <tr>
            <td><?= $category['category_name'] ?></td>
            <td>
                <a href="categories.php?edit=<?= $category['category_id'] ?>">Edit</a> |
                <a href="categories.php?delete=<?= $category['category_id'] ?>">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'components/footer.php'; ?>
