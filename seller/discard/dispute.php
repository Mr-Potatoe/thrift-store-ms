<?php

// Fetch disputes for this seller
$query_disputes = "SELECT * FROM disputes d
                   JOIN orders o ON d.order_id = o.order_id
                   WHERE o.user_id = ? AND d.status = 'open'";
$stmt_disputes = $pdo->prepare($query_disputes);
$stmt_disputes->execute([$seller_id]);
$disputes = $stmt_disputes->fetchAll();

?>

<section class="dashboard-section">
    <h2>Open Disputes</h2>
    <div class="disputes-list">
        <?php if ($disputes): ?>
            <?php foreach ($disputes as $dispute): ?>
                <div class="dispute-card">
                    <strong>Dispute ID: <?= $dispute['dispute_id'] ?></strong><br>
                    Order ID: <?= $dispute['order_id'] ?><br>
                    Issue: <?= htmlspecialchars($dispute['issue']) ?><br>
                    Status: <?= htmlspecialchars($dispute['status']) ?><br>
                    <a href="view_dispute.php?dispute_id=<?= $dispute['dispute_id'] ?>" class="btn">View Dispute</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No open disputes.</p>
        <?php endif; ?>
    </div>
</section>