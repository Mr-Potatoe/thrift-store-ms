<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Query to fetch featured items along with all their images
$query_items = "
    SELECT i.item_id, i.name, i.price 
    FROM items i
    LIMIT 10";  // Get some featured items for the buyer

$stmt_items = $pdo->query($query_items);  // Execute the query
$items = $stmt_items->fetchAll();

// Query to fetch all images for each item
$query_images = "
    SELECT ii.image_url 
    FROM item_images ii
    WHERE ii.item_id = ?";

?>

<?php include 'components/header.php'; ?>

<!-- Hero Section -->
<header class="hero bg-primary text-white text-center py-5">
    <div class="container">
        <h1 class="display-4">Welcome to Thrifted Outlet</h1>
        <p class="lead mb-4">Shop quality second-hand items at affordable prices.</p>
        <a href="categories.php" class="btn btn-light btn-lg">Browse Categories</a>
    </div>
</header>
<!-- Featured Items Section -->
<section class="featured-items py-5">
    <div class="container">
        <h2 class="text-center mb-4">Featured Items</h2>
        <div class="row">
            <?php foreach ($items as $item): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="text-decoration-none">
                            <!-- Carousel for item images -->
                            <div id="carousel-<?= $item['item_id'] ?>" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-inner">
                                    <?php
                                    // Fetch all images for the current item
                                    $stmt_images = $pdo->prepare($query_images);
                                    $stmt_images->execute([$item['item_id']]);
                                    $images = $stmt_images->fetchAll();
                                    $active_class = 'active';  // Set the first image as active
                                    foreach ($images as $index => $image):
                                    ?>
                                        <div class="carousel-item <?= $active_class ?>">
                                            <img src="../uploads/items/<?= htmlspecialchars($image['image_url']) ?>" class="d-block w-100" alt="<?= htmlspecialchars($item['name']) ?>" />
                                        </div>
                                    <?php
                                        // Remove the active class after the first item
                                        $active_class = '';
                                    endforeach;
                                    ?>
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
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title text-truncate"><?= htmlspecialchars($item['name']) ?></h5>
                                <p class="card-text text-muted">PHP <?= number_format($item['price'], 2) ?></p>
                                <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="btn btn-primary mt-auto">View Details</a>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>



<?php include 'components/footer.php'; ?>