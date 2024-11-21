<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Query to fetch featured items along with their images for buyers (no shop_id filter)
$query_items = "
    SELECT i.item_id, i.name, i.price, 
           (SELECT ii.image_url 
            FROM item_images ii 
            WHERE ii.item_id = i.item_id 
            LIMIT 1) AS image
    FROM items i
    LIMIT 10";  // Get some featured items for the buyer

$stmt_items = $pdo->query($query_items);  // Execute the query
$items = $stmt_items->fetchAll();
?>

<?php include 'components/header.php'; ?>

<!-- Header -->
<header class="hero">
    <div class="hero-content">
        <h1>Welcome to Thrifted Outlet</h1>
        <p>Shop quality second-hand items at affordable prices.</p>
        <a href="categories.php" class="cta-button">Browse Categories</a>
    </div>
</header>

<!-- Featured Items Section -->
<section class="featured-items">
    <h2>Featured Items</h2>
    <ul class="item-list">
        <?php foreach ($items as $item): ?>
            <li class="item-card">
                <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="item-link">
                    <!-- Carousel for item images -->
                    <div id="carousel-<?= $item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="../uploads/items/<?= htmlspecialchars($item['image']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($item['name']) ?>">
                            </div>
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
                    <div class="item-info">
                        <h3 class="item-name"><?= htmlspecialchars($item['name']) ?></h3>
                        <p class="item-price">$<?= number_format($item['price'], 2) ?></p>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php include 'components/footer.php'; ?>
