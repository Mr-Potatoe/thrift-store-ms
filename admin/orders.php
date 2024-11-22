 
<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();
// Fetch orders
$query = "SELECT * FROM orders";
$stmt = $pdo->query($query);
?>


<?php include 'components/header.php'; ?>
<h1>Order Management</h1>

<!-- Orders list -->
<table>
    <thead>
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
            <td><?= $order['order_id'] ?></td>
            <td><?= $order['user_id'] ?></td>
            <td><?= $order['total_price'] ?></td>
            <td><?= $order['order_status'] ?></td>
            <td>
                <a href="orders.php?view=<?= $order['order_id'] ?>">View</a> |
                <a href="orders.php?update=<?= $order['order_id'] ?>">Update</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'components/footer.php'; ?>
