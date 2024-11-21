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



<section class="categories-section">
    <h1 class="categories-title">Categories</h1>
    <p class="categories-description">Shop quality second-hand items at affordable prices.</p>
    <a href="categories.php" class="cta-button">View All Categories</a>

    <ul class="categories-list">
        <?php foreach ($categories as $category): ?>
            <li class="category-item">
                <a href="search.php?category_id=<?= $category['category_id'] ?>" class="category-link"><?= $category['category_name'] ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</section>



<?php include 'components/footer.php'; ?>