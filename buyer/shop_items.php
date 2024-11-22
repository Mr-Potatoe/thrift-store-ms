<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get shop ID from URL
if (!isset($_GET['shop_id'])) {
    die('Shop ID is missing.');
}

$shop_id = $_GET['shop_id'];

// Get shop details
$query_shop = "SELECT * FROM shops WHERE shop_id = :shop_id";
$stmt_shop = $pdo->prepare($query_shop);
$stmt_shop->execute([':shop_id' => $shop_id]);
$shop = $stmt_shop->fetch();

if (!$shop) {
    die('Shop not found.');
}

// Get items for the shop
$query_items = "SELECT i.*, s.shop_name AS shop_name FROM items i
                JOIN shops s ON i.shop_id = s.shop_id
                WHERE i.shop_id = :shop_id";
$stmt_items = $pdo->prepare($query_items);
$stmt_items->execute([':shop_id' => $shop_id]);
$items = $stmt_items->fetchAll();
?>

<?php include 'components/header.php'; ?>

<section class="shop-details py-5">
    <div class="container">
        <div class="text-center mb-5">
            <h1 class="display-4"><?= htmlspecialchars($shop['shop_name']) ?> - Shop</h1>
            <p class="lead text-muted"><?= htmlspecialchars($shop['shop_description']) ?></p>
        </div>

        <h2 class="text-center mb-4">Items Available in This Shop</h2>

        <div class="row">
            <?php if (count($items) > 0): ?>
                <?php foreach ($items as $item): ?>
                    <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
                        <div class="card shadow-sm h-100">
                            <!-- Item Image Carousel -->
                            <div class="carousel-container">
                                <div id="carouselItem<?= $item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                    <div class="carousel-inner">
                                        <?php
                                        // Get images for the current item
                                        $query_images = "SELECT * FROM item_images WHERE item_id = :item_id";
                                        $stmt_images = $pdo->prepare($query_images);
                                        $stmt_images->execute([':item_id' => $item['item_id']]);
                                        $images = $stmt_images->fetchAll();
                                        ?>
                                        <?php if (count($images) > 0): ?>
                                            <?php foreach ($images as $index => $image): ?>
                                                <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                                                    <img src="../uploads/items/<?= htmlspecialchars($image['image_url']) ?>" class="d-block w-100" alt="Item Image">
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="carousel-item active">
                                                <img src="path/to/default-image.jpg" class="d-block w-100" alt="No Image Available">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselItem<?= $item['item_id'] ?>" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Previous</span>
                                    </button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#carouselItem<?= $item['item_id'] ?>" data-bs-slide="next">
                                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                        <span class="visually-hidden">Next</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Card Body -->
                            <div class="card-body d-flex flex-column">
                                <h3 class="card-title"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="card-text"><?= htmlspecialchars($item['description']) ?></p>
                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                    <p class="item-price mb-0"><strong>Price:</strong> PHP <?= number_format($item['price'], 2) ?></p>
                                    <p class="item-condition mb-0"><strong>Condition:</strong> <?= ucfirst($item['condition']) ?></p>
                                </div>
                                <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="btn btn-primary mt-3">View Item</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p>No items available in this shop yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>



<?php include 'components/footer.php'; ?>