 <?php
    // Include database connection and session management
    require_once '../config/database.php';
    session_start();

    // Check if user is logged in and is an admin
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../login.php");
        exit;
    }

    // Fetch all users from the database
    $query = "SELECT * FROM users";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $users = $stmt->fetchAll();
    ?>

 <?php include 'components/header.php'; ?>


 <div class="container my-5">
     <div class="d-flex justify-content-between align-items-center mb-4">
         <h1>Manage Users</h1>
         <a href="create_user.php" class="btn btn-primary">Create New User</a>
     </div>

     <div class="table-responsive">
         <table class="table table-bordered table-hover">
             <thead class="table-dark">
                 <tr>
                     <th>Username</th>
                     <th>Email</th>
                     <th>Role</th>
                     <th>Actions</th>
                 </tr>
             </thead>
             <tbody>
                 <?php foreach ($users as $user): ?>
                     <tr>
                         <td><?php echo htmlspecialchars($user['username']); ?></td>
                         <td><?php echo htmlspecialchars($user['email']); ?></td>
                         <td><?php echo ucfirst(htmlspecialchars($user['role'])); ?></td>
                         <td>
                             <a href="edit_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                             <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-danger btn-sm"
                                 onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                         </td>
                     </tr>
                 <?php endforeach; ?>
             </tbody>
         </table>
     </div>
 </div>
 <?php include 'components/footer.php'; ?>