<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch seller's shop details
$query = "SELECT * FROM shops WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$seller_id]);
$shop = $stmt->fetch();

// Pagination logic
$limit = 10;  // Number of feedbacks per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Fetch feedback for the seller's shop with pagination
$query_feedback = "SELECT f.*, o.user_id AS buyer_id, u.username FROM feedback f
                   JOIN orders o ON f.order_id = o.order_id
                   JOIN users u ON o.user_id = u.user_id
                   WHERE f.shop_id = ?
                   ORDER BY f.created_at DESC
                   LIMIT ? OFFSET ?";
$stmt_feedback = $pdo->prepare($query_feedback);
$stmt_feedback->execute([$shop['shop_id'], $limit, $offset]);
$feedback = $stmt_feedback->fetchAll();

// Fetch the total number of feedbacks to calculate pagination
$query_total_feedback = "SELECT COUNT(*) FROM feedback WHERE shop_id = ?";
$stmt_total_feedback = $pdo->prepare($query_total_feedback);
$stmt_total_feedback->execute([$shop['shop_id']]);
$total_feedback = $stmt_total_feedback->fetchColumn();
$total_pages = ceil($total_feedback / $limit);

// Handle feedback deletion
if (isset($_GET['delete_feedback_id'])) {
    $feedback_id = (int)$_GET['delete_feedback_id'];
    try {
        // Delete the feedback from the database
        $query_delete = "DELETE FROM feedback WHERE feedback_id = ?";
        $stmt_delete = $pdo->prepare($query_delete);
        $stmt_delete->execute([$feedback_id]);

        // Redirect to the same page with a success message
        header("Location: manage_feedback.php?success=Feedback deleted successfully");
        exit;
    } catch (PDOException $e) {
        $error = "Error deleting feedback: " . $e->getMessage();
    }
}

// Handle feedback moderation (optional response or flagging)
if (isset($_POST['moderate_feedback_id'])) {
    $feedback_id = (int)$_POST['moderate_feedback_id'];
    $response = $_POST['response'] ?? '';

    try {
        // Update the feedback with a seller's response (or flagging as addressed)
        $query_moderate = "UPDATE feedback SET response = ? WHERE feedback_id = ?";
        $stmt_moderate = $pdo->prepare($query_moderate);
        $stmt_moderate->execute([$response, $feedback_id]);

        // Redirect to the same page with a success message
        header("Location: manage_feedback.php?success=Feedback moderated successfully");
        exit;
    } catch (PDOException $e) {
        $error = "Error moderating feedback: " . $e->getMessage();
    }
}

?>

<?php include 'components/header.php'; ?>

<main class="dashboard-content">
    <h1>Manage Feedback</h1>
    <div class="dashboard-content">
        <h2>Feedback for Your Shop</h2>

        <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>
        <?php if (isset($_GET['success'])) { echo "<p class='success-message'>{$_GET['success']}</p>"; } ?>

        <ul>
            <?php foreach ($feedback as $fb): ?>
                <li>
                    <strong>Buyer: <?= htmlspecialchars($fb['username']) ?> (Rating: <?= $fb['rating'] ?>/5)</strong><br>
                    Comment: <?= htmlspecialchars($fb['comment']) ?><br>
                    Date: <?= $fb['created_at'] ?><br>

                    <!-- Option to delete feedback -->
                    <a href="manage_feedback.php?delete_feedback_id=<?= $fb['feedback_id'] ?>" onclick="return confirm('Are you sure you want to delete this feedback?')">Delete Feedback</a>

                    <!-- Feedback moderation (response from seller) -->
                    <form method="POST" action="manage_feedback.php" style="margin-top: 10px;">
                        <input type="hidden" name="moderate_feedback_id" value="<?= $fb['feedback_id'] ?>">
                        <textarea name="response" placeholder="Your response to this feedback (optional)"><?= $fb['response'] ?? '' ?></textarea><br>
                        <button type="submit" class="btn">Respond to Feedback</button>
                    </form>
                </li>
                <hr>
            <?php endforeach; ?>
        </ul>

        <!-- Pagination -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="manage_feedback.php?page=<?= $page - 1 ?>">Previous</a>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="manage_feedback.php?page=<?= $page + 1 ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'components/footer.php'; ?>
