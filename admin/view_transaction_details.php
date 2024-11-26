<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get the transaction ID from the query string
$transaction_id = $_GET['id'];

// Fetch transaction details from the database
$query = "SELECT u.username AS buyer, s.username AS seller, i.name AS item_name, 
                  oi.quantity, oi.price AS total_price, o.order_date AS transaction_date, o.payment_status
          FROM orders o
          JOIN order_items oi ON o.order_id = oi.order_id
          JOIN items i ON oi.item_id = i.item_id
          JOIN users u ON o.user_id = u.user_id
          JOIN users s ON i.shop_id = s.user_id  -- Use shop_id to link to the seller (user)
          WHERE o.order_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$transaction_id]);
$transaction = $stmt->fetch();

// Check if the transaction exists
if ($transaction === false) {
    header("Location: view_transactions.php");
    echo "Transaction not found!";
    exit;
}
?>
<?php include 'components/header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Transaction Details</h1>

    <!-- Transaction Details Table -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <tbody>
                <tr>
                    <th>Buyer</th>
                    <td><?= htmlspecialchars($transaction['buyer']); ?></td>
                </tr>
                <tr>
                    <th>Seller</th>
                    <td><?= htmlspecialchars($transaction['seller']); ?></td>
                </tr>
                <tr>
                    <th>Item</th>
                    <td><?= htmlspecialchars($transaction['item_name']); ?></td>
                </tr>
                <tr>
                    <th>Quantity</th>
                    <td><?= htmlspecialchars($transaction['quantity']); ?></td>
                </tr>
                <tr>
                    <th>Total Price</th>
                    <td>₱<?= number_format($transaction['total_price'], 2); ?></td>
                </tr>
                <tr>
                    <th>Transaction Date</th>
                    <td><?= htmlspecialchars($transaction['transaction_date']); ?></td>
                </tr>
                <tr>
                    <th>Payment Status</th>
                    <td><?= htmlspecialchars($transaction['payment_status']); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Back Button -->
    <a href="view_transactions.php" class="btn btn-primary">Back to Transactions</a>
</div>

<?php include 'components/footer.php'; ?>
