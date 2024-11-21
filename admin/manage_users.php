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


 <h1>Manage Users</h1>
 <a href="create_user.php">Create New User</a>
 <table border="1">
     <thead>
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
                 <td><?php echo $user['username']; ?></td>
                 <td><?php echo $user['email']; ?></td>
                 <td><?php echo ucfirst($user['role']); ?></td>
                 <td>
                     <a href="edit_user.php?id=<?php echo $user['user_id']; ?>">Edit</a>
                     <a href="delete_user.php?id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure?')">Delete</a>
                 </td>
             </tr>
         <?php endforeach; ?>
     </tbody>
 </table>
 <?php include 'components/footer.php'; ?>