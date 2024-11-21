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

<h2>Categories</h2>
<ul>
    <?php foreach ($categories as $category): ?>
        <li><a href="search.php?category_id=<?= $category['category_id'] ?>"><?= $category['category_name'] ?></a></li>
    <?php endforeach; ?>
</ul>


<?php include 'components/footer.php'; ?>