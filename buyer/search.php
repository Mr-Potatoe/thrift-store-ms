<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get category filter
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

// Build query based on filters
$query = "SELECT * FROM items WHERE name LIKE :search_term";
if ($category_id) {
    $query .= " AND category_id = :category_id";
}

$stmt = $pdo->prepare($query);
$stmt->execute([
    ':search_term' => '%' . $search_term . '%',
    ':category_id' => $category_id,
]);

$items = $stmt->fetchAll();
?>

<?php include 'components/header.php'; ?>
<section class="search-results">
    <h1 class="search-results-header">Search Results</h1>

    <form method="GET" action="search.php" class="search-form">
        <input type="text" name="search_term" placeholder="Search for items" value="<?= htmlspecialchars($search_term) ?>" class="search-input">
        <button type="submit" class="search-button">Search</button>
    </form>

    <ul class="search-results-list">
        <?php foreach ($items as $item): ?>
            <li class="search-item">
                <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="search-item-link">
                    <?= $item['name'] ?>
                </a> - $<?= number_format($item['price'], 2) ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<?php include 'components/footer.php'; ?>