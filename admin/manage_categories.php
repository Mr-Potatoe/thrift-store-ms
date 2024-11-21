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


 <h1>Manage Categories</h1>
 <a href="create_category.php">Create New Category</a>
 <table border="1">
     <thead>
         <tr>
             <th>Category Name</th>
             <th>Actions</th>
         </tr>
     </thead>
     <tbody>
         <?php foreach ($categories as $category): ?>
             <tr>
                 <td><?php echo $category['category_name']; ?></td>
                 <td>
                     <a href="edit_category.php?id=<?php echo $category['category_id']; ?>">Edit</a>
                     <a href="delete_category.php?id=<?php echo $category['category_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                 </td>
             </tr>
         <?php endforeach; ?>
     </tbody>
 </table>
 <?php include 'components/footer.php'; ?>