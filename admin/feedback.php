<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Fetch feedback
$query = "SELECT * FROM feedback";
$stmt = $pdo->query($query);
?>
<?php include 'components/header.php'; ?>
<h1>Feedback Management</h1>

<!-- Feedback list -->
<table>
    <thead>
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
            <td><?= $feedback['order_id'] ?></td>
            <td><?= $feedback['user_id'] ?></td>
            <td><?= $feedback['rating'] ?></td>
            <td><?= $feedback['comment'] ?></td>
            <td><?= $feedback['response'] ?></td>
            <td>
                <a href="feedback.php?respond=<?= $feedback['feedback_id'] ?>">Respond</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'components/footer.php'; ?>
