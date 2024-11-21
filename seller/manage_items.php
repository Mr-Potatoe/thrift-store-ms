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

$query_items = "
    SELECT i.*, GROUP_CONCAT(im.image_url) AS image_urls
    FROM items i
    LEFT JOIN item_images im ON i.item_id = im.item_id
    WHERE i.shop_id = ?
    GROUP BY i.item_id
";
$stmt_items = $pdo->prepare($query_items);
$stmt_items->execute([$shop['shop_id']]);
$items = $stmt_items->fetchAll();


// Handle delete item action
if (isset($_GET['delete_item_id'])) {
    $item_id = $_GET['delete_item_id'];

    // Delete the item from the database
    $delete_query = "DELETE FROM items WHERE item_id = ?";
    $stmt_delete = $pdo->prepare($delete_query);
    $stmt_delete->execute([$item_id]);

    // Redirect back to the manage items page after deletion
    header("Location: manage_items.php");
    exit;
}
?>

<?php include 'components/header.php'; ?>


<main>
    <h1 style="color: #2d3748; font-size: 2rem; margin-bottom: 20px;">Manage Your Items, <?= htmlspecialchars($shop['shop_name']) ?></h1>

    <!-- Items List Section -->
    <div class="dashboard-section">
        <h2>Your Items</h2>
        <a href="add_item.php" class="btn">Add New Item</a>
        <ul class="items-list">
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <li class="item-card">
                        <!-- Carousel for item images -->
                        <div id="carousel-<?= $item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner">
                                <?php
                                $images = explode(',', $item['image_urls']);
                                foreach ($images as $index => $image): ?>
                                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="../uploads/items/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="d-block w-100">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button class="carousel-control-prev" type="button" data-bs-target="#carousel-<?= $item['item_id'] ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Previous</span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#carousel-<?= $item['item_id'] ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="visually-hidden">Next</span>
                            </button>
                        </div>

                        <!-- Item details -->
                        <div class="item-info">
                            <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                            <span>Price: $<?= number_format($item['price'], 2) ?></span><br>
                            <span>Quantity: <?= $item['quantity'] ?></span><br>
                            <span>Condition: <?= ucfirst($item['condition']) ?></span><br>
                        </div>
                        <div class="action-buttons">
                            <a class="btn" href="edit_item.php?item_id=<?= $item['item_id'] ?>">Edit</a>
                            <a class="btn" href="manage_items.php?delete_item_id=<?= $item['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="item-card">
                    <div class="item-info">
                        <strong>No items listed yet.</strong>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</main>



<?php include 'components/footer.php'; ?>

</body>

</html>