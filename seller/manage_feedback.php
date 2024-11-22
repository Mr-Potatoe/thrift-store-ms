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
$query_feedback = "SELECT f.*, o.user_id AS buyer_id, u.username, i.name AS item_name 
                   FROM feedback f
                   JOIN orders o ON f.order_id = o.order_id
                   JOIN users u ON o.user_id = u.user_id
                   JOIN order_items oi ON oi.order_id = o.order_id
                   JOIN items i ON oi.item_id = i.item_id   -- Get item name from order_items and items
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

<main class="dashboard-content container py-4">
    <h1 class="text-primary mb-4">Manage Feedback</h1>
    <section class="feedback-section">
        <h2 class="mb-4">Feedback for Your Shop</h2>

        <!-- Error and Success Messages -->
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>

        <!-- Feedback List -->
        <div class="feedback-list">
            <?php foreach ($feedback as $fb): ?>
                <div class="feedback-card card mb-4">
                    <div class="card-body">
                        <div class="feedback-header mb-3">
                            <h5 class="card-title">
                                Buyer: <?= htmlspecialchars($fb['username']) ?>
                                <span class="badge bg-primary">Rating: <?= $fb['rating'] ?>/5</span>
                            </h5>
                            <p class="card-text"><strong>Item:</strong> <?= htmlspecialchars($fb['item_name']) ?></p>
                        </div>
                        <p><strong>Comment:</strong> <?= htmlspecialchars($fb['comment']) ?></p>
                        <p><strong>Date:</strong> <?= $fb['created_at'] ?></p>

                        <!-- Seller's Response -->
                        <?php if (!empty($fb['response'])): ?>
                            <div class="seller-response mt-3 p-3 bg-light border rounded">
                                <strong>Seller's Response:</strong>
                                <p><?= htmlspecialchars($fb['response']) ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Feedback Actions -->
                        <div class="feedback-actions mt-4">
                            <a href="manage_feedback.php?delete_feedback_id=<?= $fb['feedback_id'] ?>"
                                class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure you want to delete this feedback?')">
                                Delete Feedback
                            </a>

                            <!-- Feedback Response Form -->
                            <form method="POST" action="manage_feedback.php" class="response-form mt-3">
                                <input type="hidden" name="moderate_feedback_id" value="<?= $fb['feedback_id'] ?>">
                                <textarea name="response" class="form-control mb-2" rows="3" placeholder="Your response to this feedback (optional)"><?= $fb['response'] ?? '' ?></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Respond to Feedback</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <div class="pagination d-flex justify-content-between align-items-center mt-4">
            <?php if ($page > 1): ?>
                <a href="manage_feedback.php?page=<?= $page - 1 ?>" class="btn btn-secondary btn-sm">Previous</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
            <span>Page <?= $page ?> of <?= $total_pages ?></span>
            <?php if ($page < $total_pages): ?>
                <a href="manage_feedback.php?page=<?= $page + 1 ?>" class="btn btn-secondary btn-sm">Next</a>
            <?php else: ?>
                <span></span>
            <?php endif; ?>
        </div>
    </section>
</main>


<?php include 'components/footer.php'; ?>