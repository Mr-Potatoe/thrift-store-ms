<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}


// Updated query to fetch order and payment details
$query = "
SELECT 
    u.username AS buyer,
    i.name AS item_name,
    oi.quantity,
    o.total_price,
    o.order_date AS transaction_date,
    o.order_id
FROM orders o
LEFT JOIN order_items oi ON o.order_id = oi.order_id
LEFT JOIN items i ON oi.item_id = i.item_id
LEFT JOIN users u ON o.user_id = u.user_id
ORDER BY o.order_date DESC

";
$stmt = $pdo->prepare($query);
$stmt->execute();
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php include 'components/header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">View Transactions</h1>

    <!-- Transactions Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Buyer</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Transaction Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?= htmlspecialchars($transaction['buyer']); ?></td>
                        <td><?= htmlspecialchars($transaction['item_name']); ?></td>
                        <td><?= htmlspecialchars($transaction['quantity']); ?></td>
                        <td>₱<?= number_format($transaction['total_price'], 2); ?></td>
                        <td><?= htmlspecialchars($transaction['transaction_date']); ?></td>
                        <td>
                            <a href="view_transaction_details.php?id=<?= $transaction['order_id']; ?>" class="btn btn-info btn-sm">View Details</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'components/footer.php'; ?>