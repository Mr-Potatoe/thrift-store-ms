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


<main class="dashboard-content">
    <!-- Title -->
    <div class="container mt-5">
        <h1 class="text-primary mb-4">Manage Your Items, <?= htmlspecialchars($shop['shop_name']) ?></h1>

        <!-- Add New Item Button -->
        <a href="add_item.php" class="btn btn-success mb-4">Add New Item</a>

        <!-- Items List Section -->
        <section class="dashboard-section">
            <h2>Your Items</h2>
            <div class="row">
                <?php if ($items): ?>
                    <?php foreach ($items as $item): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card">
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
                                <div class="card-body">
                                    <h5 class="card-title"><?= htmlspecialchars($item['name']) ?></h5>
                                    <p class="card-text">Price: PHP. <?= number_format($item['price'], 2) ?></p>
                                    <p class="card-text">Quantity: <?= $item['quantity'] ?></p>
                                    <p class="card-text">Condition: <?= ucfirst($item['condition']) ?></p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="card-footer d-flex justify-content-between">
                                    <a class="btn btn-warning" href="edit_item.php?item_id=<?= $item['item_id'] ?>">Edit</a>
                                    <a class="btn btn-danger" href="manage_items.php?delete_item_id=<?= $item['item_id'] ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-info">No items listed yet.</div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>




<?php include 'components/footer.php'; ?>

</body>

</html>