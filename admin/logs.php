<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Fetch logs
$query = "SELECT * FROM logs ORDER BY log_date DESC";
$stmt = $pdo->query($query);
?>
<?php include 'components/header.php'; ?>
<h1>System Logs</h1>

<!-- Logs list -->
<table>
    <thead>
        <tr>
            <th>User ID</th>
            <th>Action</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($log = $stmt->fetch()): ?>
        <tr>
            <td><?= $log['user_id'] ?></td>
            <td><?= $log['action'] ?></td>
            <td><?= $log['log_date'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'components/footer.php'; ?>