<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get all shops
$query_shops = "SELECT * FROM shops";
$stmt_shops = $pdo->prepare($query_shops);
$stmt_shops->execute();
$shops = $stmt_shops->fetchAll();
?>

<?php include 'components/header.php'; ?>

<!-- Shops List Section -->
<section class="shops-list py-5">
    <div class="container">
        <h1 class="text-center mb-4">Shops</h1>

        <!-- Display Shops -->
        <div class="row">
            <?php if (count($shops) > 0): ?>
                <?php foreach ($shops as $shop): ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-primary text-white text-center">
                                <h3 class="shop-name"><?= htmlspecialchars($shop['shop_name']) ?></h3>
                            </div>
                            <div class="card-body">
                                <p class="shop-description"><?= htmlspecialchars($shop['shop_description']) ?></p>
                            </div>
                            <div class="card-footer text-center">
                                <a href="shop_items.php?shop_id=<?= $shop['shop_id'] ?>" class="btn btn-primary">View Items</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No shops available.</p>
            <?php endif; ?>
        </div>
    </div>
</section>



<?php include 'components/footer.php'; ?>