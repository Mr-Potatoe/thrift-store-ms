 <?php
    // Include database connection and session management
    require_once '../config/database.php';
    session_start();

    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    // Fetch all categories from the database
    $query = "SELECT * FROM categories";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $categories = $stmt->fetchAll();
    ?>

 <?php include 'components/header.php'; ?>

 <div class="container my-5">
     <div class="d-flex justify-content-between align-items-center mb-4">
         <h1>Manage Categories</h1>
         <a href="create_category.php" class="btn btn-primary">Create New Category</a>
     </div>

     <div class="table-responsive">
         <table class="table table-bordered table-hover">
             <thead class="table-dark">
                 <tr>
                     <th>Category Name</th>
                     <th>Actions</th>
                 </tr>
             </thead>
             <tbody>
                 <?php foreach ($categories as $category): ?>
                     <tr>
                         <td><?php echo htmlspecialchars($category['category_name']); ?></td>
                         <td>
                             <a href="edit_category.php?id=<?php echo $category['category_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                             <a href="delete_category.php?id=<?php echo $category['category_id']; ?>" class="btn btn-danger btn-sm"
                                 onclick="return confirm('Are you sure you want to delete this category?')">Delete</a>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>
     </div>
 </div>

 <?php include 'components/footer.php'; ?>