<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Fetch feedback
$query = "SELECT * FROM feedback";
$stmt = $pdo->query($query);
?>

<?php include 'components/header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Feedback Management</h1>

    <!-- Feedback List -->
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>User ID</th>
                    <th>Rating</th>
                    <th>Comment</th>
                    <th>Response</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($feedback = $stmt->fetch()): ?>
                    <tr>
                        <td><?= htmlspecialchars($feedback['order_id']) ?></td>
                        <td><?= htmlspecialchars($feedback['user_id']) ?></td>
                        <td>
                            <span class="badge bg-<?= $feedback['rating'] >= 4 ? 'success' : ($feedback['rating'] >= 2 ? 'warning' : 'danger') ?>">
                                <?= htmlspecialchars($feedback['rating']) ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($feedback['comment']) ?></td>
                        <td><?= htmlspecialchars($feedback['response'] ?: 'No response yet') ?></td>
                        <td>
                            <a href="feedback.php?respond=<?= $feedback['feedback_id'] ?>" class="btn btn-primary btn-sm">Respond</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'components/footer.php'; ?>