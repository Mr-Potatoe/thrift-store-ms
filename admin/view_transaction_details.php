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

    <h1>Transaction Details</h1>
    <table border="1">
        <tr>
            <td>Buyer</td>
            <td><?php echo htmlspecialchars($transaction['buyer']); ?></td>
        </tr>
        <tr>
            <td>Seller</td>
            <td><?php echo htmlspecialchars($transaction['seller']); ?></td>
        </tr>
        <tr>
            <td>Item</td>
            <td><?php echo htmlspecialchars($transaction['item_name']); ?></td>
        </tr>
        <tr>
            <td>Quantity</td>
            <td><?php echo htmlspecialchars($transaction['quantity']); ?></td>
        </tr>
        <tr>
            <td>Total Price</td>
            <td><?php echo htmlspecialchars($transaction['total_price']); ?></td>
        </tr>
        <tr>
            <td>Transaction Date</td>
            <td><?php echo htmlspecialchars($transaction['transaction_date']); ?></td>
        </tr>
        <tr>
            <td>Payment Status</td>
            <td><?php echo htmlspecialchars($transaction['payment_status']); ?></td>
        </tr>
    </table>

    <a href="view_transactions.php">Back to Transactions</a>

    <?php include 'components/footer.php'; ?>