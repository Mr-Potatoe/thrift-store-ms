<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get item ID
$item_id = $_GET['item_id'];

// Get item details
$query = "SELECT * FROM items WHERE item_id = :item_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':item_id' => $item_id]);
$item = $stmt->fetch();

// Get item images
$query_images = "SELECT * FROM item_images WHERE item_id = :item_id";
$stmt_images = $pdo->prepare($query_images);
$stmt_images->execute([':item_id' => $item_id]);
$images = $stmt_images->fetchAll();

// Get the number of items in the user's cart
$query_cart_count = "SELECT COUNT(*) AS item_count FROM cart WHERE user_id = ?";
$stmt_cart_count = $pdo->prepare($query_cart_count);
$stmt_cart_count->execute([$_SESSION['user_id']]);
$cart_count = $stmt_cart_count->fetch();

// Default to 0 if no items in cart
$item_count = $cart_count['item_count'] ?? 0;
?>

<?php include 'components/header.php'; ?>

<section class="item-details">
    <!-- Item Details Section -->
    <h1 class="item-title"><?= htmlspecialchars($item['name']) ?></h1>

    <div class="item-info">
        <p class="item-description"><?= htmlspecialchars($item['description']) ?></p>
        <p class="item-condition"><strong>Condition:</strong> <?= ucfirst($item['condition']) ?></p>
        <p class="item-price"><strong>Price:</strong> $<?= number_format($item['price'], 2) ?></p>
        <p class="item-stock"><strong>Available Stock:</strong> <?= $item['quantity'] ?></p>
    </div>

    <!-- Item Images Gallery (Bootstrap Carousel) -->
    <section class="item-images-gallery">
        <h2>Item Images</h2>
        <div id="itemCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $image): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="../uploads/items/<?= htmlspecialchars($image['image_url']) ?>" alt="Item Image" class="carousel-image d-block w-100">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#itemCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#itemCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </section>

    <!-- Add to Cart Section -->
    <section class="add-to-cart">
        <form action="cart.php" method="POST" class="cart-form">
            <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
            <label for="quantity" class="quantity-label">Quantity:</label>
            <input type="number" id="quantity" name="quantity" min="1" max="<?= $item['quantity'] ?>" value="1" required class="quantity-input">
            <button type="submit" class="add-to-cart-btn">Add to Cart</button>
        </form>
    </section>
</section>


<?php include 'components/footer.php'; ?>