<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Get the dispute ID from the query string
$dispute_id = $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get dispute status from the form
    $dispute_status = $_POST['dispute_status'];

    // Update the dispute status in the database
    $query = "UPDATE disputes SET status = ? WHERE dispute_id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$dispute_status, $dispute_id])) {
        echo "Dispute status updated successfully!";
        header("Location: view_disputes.php");
        exit;
    } else {
        echo "Error updating dispute status.";
    }
}

// Fetch current dispute status from the database
$query = "SELECT status FROM disputes WHERE dispute_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$dispute_id]);
$dispute = $stmt->fetch();

// Check if dispute was found
if (!$dispute) {
    echo "No dispute found with the given ID.";
    exit;
}
?>

<?php include 'components/header.php'; ?>

<h1>Resolve Dispute</h1>
<form method="POST">
    <label for="dispute_status">Dispute Status:</label>
    <select name="dispute_status">
        <option value="open" <?php echo $dispute['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
        <option value="in_progress" <?php echo $dispute['status'] === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
        <option value="resolved" <?php echo $dispute['status'] === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
        <option value="closed" <?php echo $dispute['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
    </select><br>
    <button type="submit">Update Status</button>
</form>

<a href="view_transactions.php">Back to View Transactions</a>


<?php include 'components/footer.php'; ?>