<?php
// Include database connection
require_once '../config/database.php';
session_start();
// Get categories
$query = "SELECT * FROM categories";
$stmt = $pdo->query($query);
$categories = $stmt->fetchAll();

?>

<?php include 'components/header.php'; ?>



<!-- Categories Section -->
<section class="categories-section py-5">
    <div class="container">
        <h1 class="categories-title text-center mb-4">Categories</h1>
        <p class="categories-description text-center mb-4">Shop quality second-hand items at affordable prices.</p>

        <div class="row">
            <?php foreach ($categories as $category): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                    <div class="card shadow-sm">
                        <a href="search.php?category_id=<?= $category['category_id'] ?>" class="category-link text-decoration-none">
                            <div class="card-body text-center">
                                <h5 class="card-title"><?= htmlspecialchars($category['category_name']) ?></h5>
                            </div>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>





<?php include 'components/footer.php'; ?>