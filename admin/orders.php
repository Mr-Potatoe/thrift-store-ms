 <?php
    // Include database connection and session management
    require_once '../config/database.php';
    session_start();
    // Fetch orders
    $query = "SELECT * FROM orders";
    $stmt = $pdo->query($query);
    ?>

 <?php include 'components/header.php'; ?>

 <div class="container my-5">
     <h1 class="text-center mb-4">Order Management</h1>

     <!-- Orders List -->
     <div class="table-responsive">
         <table class="table table-bordered table-hover">
             <thead class="table-dark">
                 <tr>
                     <th>Order ID</th>
                     <th>User ID</th>
                     <th>Total Price</th>
                     <th>Order Status</th>
                     <th>Actions</th>
                 </tr>
             </thead>
             <tbody>
                 <?php while ($order = $stmt->fetch()): ?>
                     <tr>
                         <td><?= htmlspecialchars($order['order_id']) ?></td>
                         <td><?= htmlspecialchars($order['user_id']) ?></td>
                         <td>₱<?= number_format($order['total_price'], 2) ?></td>
                         <td><?= htmlspecialchars($order['order_status']) ?></td>
                         <td>
                             <a href="orders.php?view=<?= $order['order_id'] ?>" class="btn btn-info btn-sm">View</a>
                             <a href="orders.php?update=<?= $order['order_id'] ?>" class="btn btn-primary btn-sm">Update</a>
                         </td>
                     </tr>
                 <?php endwhile; ?>
             </tbody>
         </table>
     </div>
 </div>

 <?php include 'components/footer.php'; ?>