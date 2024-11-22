<?php
// Include database connection
require_once '../config/database.php';
session_start();

// Get category filter and search term
$category_id = isset($_GET['category_id']) ? $_GET['category_id'] : null;
$search_term = isset($_GET['search_term']) ? $_GET['search_term'] : '';

// Fetch the category name if category_id is provided
$category_name = '';
if ($category_id) {
    $category_query = "SELECT category_name FROM categories WHERE category_id = :category_id";
    $stmt = $pdo->prepare($category_query);
    $stmt->execute([':category_id' => $category_id]);
    $category = $stmt->fetch();
    if ($category) {
        $category_name = $category['category_name'];
    }
}

// Build query based on filters
$query = "SELECT * FROM items WHERE name LIKE :search_term";
$params = [':search_term' => '%' . $search_term . '%'];
if ($category_id) {
    $query .= " AND category_id = :category_id";
    $params[':category_id'] = $category_id;
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$items = $stmt->fetchAll();
?>

<?php include 'components/header.php'; ?>
<section class="search-results container mt-5">
    <h1 class="search-results-header text-primary mb-4">
        <?php if ($category_name): ?>
            "<?= htmlspecialchars($category_name) ?>" Category
        <?php else: ?>
            Search Results
        <?php endif; ?>
    </h1>

    <!-- Search Form -->
    <form method="GET" action="search.php" class="search-form d-flex mb-4">
        <input
            type="text"
            name="search_term"
            class="form-control me-2"
            placeholder="Search for items"
            value="<?= htmlspecialchars($search_term) ?>">
        <?php if ($category_id): ?>
            <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <!-- Search Results List -->
    <ul class="list-group">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <a href="view_item.php?item_id=<?= $item['item_id'] ?>" class="text-decoration-none text-dark fw-bold">
                        <?= htmlspecialchars($item['name']) ?>
                    </a>
                    <span class="badge bg-success text-light">PHP <?= number_format($item['price'], 2) ?></span>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li class="list-group-item text-center text-muted">No items found for this search.</li>
        <?php endif; ?>
    </ul>
</section>


<?php include 'components/footer.php'; ?>