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

<div class="container my-5">
    <h1 class="text-center mb-4">Manage Categories</h1>

    <!-- Add Category Form -->
    <div class="card mb-4 shadow">
        <div class="card-body">
            <form method="POST" class="row g-3">
                <div class="col-md-9">
                    <input type="text" name="category_name" class="form-control" placeholder="Category Name" required>
                </div>
                <div class="col-md-3">
                    <button type="submit" name="add_category" class="btn btn-primary w-100">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories List -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = $stmt->fetch()): ?>
                    <tr>
                        <td><?= htmlspecialchars($category['category_name']) ?></td>
                        <td>
                            <a href="categories.php?edit=<?= $category['category_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="categories.php?delete=<?= $category['category_id'] ?>" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'components/footer.php'; ?>