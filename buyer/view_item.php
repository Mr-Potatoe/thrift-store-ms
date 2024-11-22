<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get item ID
$item_id = $_GET['item_id'];

// Get item details and associated shop name
$query = "SELECT i.*, s.shop_name AS shop_name FROM items i
          JOIN shops s ON i.shop_id = s.shop_id
          WHERE i.item_id = :item_id";
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

<section class="item-details container py-5">
    <!-- Bootstrap Card -->
    <div class="card shadow-sm">
        <!-- Card Header -->
        <div class="card-header text-center bg-light">
            <h1 class="card-title h4"><?= htmlspecialchars($item['name']) ?></h1>
        </div>

        <!-- Card Body -->
        <div class="card-body">
            <!-- Item Description -->
            <p class="item-description mb-3"><?= htmlspecialchars($item['description']) ?></p>

            <!-- Item Information -->
            <div class="mb-4">
                <p class="item-condition"><strong>Condition:</strong> <?= ucfirst($item['condition']) ?></p>
                <p class="item-price"><strong>Price:</strong> PHP <?= number_format($item['price'], 2) ?></p>
                <p class="item-stock"><strong>Available Stock:</strong> <?= $item['quantity'] ?></p>
                <p class="item-shop"><strong>Shop:</strong> <?= htmlspecialchars($item['shop_name']) ?></p>
            </div>

            <!-- Item Images Gallery (Bootstrap Carousel) -->
            <h2 class="h5 mb-3">Item Images</h2>
            <div id="itemCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php foreach ($images as $index => $image): ?>
                        <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                            <img src="../uploads/items/<?= htmlspecialchars($image['image_url']) ?>" alt="Item Image" class="d-block mx-auto carousel-image">
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Carousel Control Buttons -->
                <button class="carousel-control-prev position-absolute top-50 start-0 translate-middle-y" type="button" data-bs-target="#itemCarousel" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>

                <button class="carousel-control-next position-absolute top-50 end-0 translate-middle-y" type="button" data-bs-target="#itemCarousel" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>

            <!-- Add to Cart Section -->
            <form action="cart.php" method="POST" class="cart-form d-flex flex-column align-items-start">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="item_id" value="<?= htmlspecialchars($item['item_id']) ?>">

                <div class="mb-3 w-100">
                    <label for="quantity" class="form-label">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" max="<?= $item['quantity'] ?>" value="1" required class="form-control w-auto">
                </div>

                <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
            </form>
        </div>
    </div>
</section>

<?php include 'components/footer.php'; ?>