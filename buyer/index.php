<?php
// Include database connection
require_once '../config/database.php';
session_start();



$query_items = "SELECT * FROM items LIMIT 10"; // Get some featured items
$stmt_items = $pdo->query($query_items);
$items = $stmt_items->fetchAll();
?>

<?php include 'components/header.php'; ?>

<header>
        <h1>Welcome to Thrifted Outlet</h1>
    </header>
    <h2>Featured Items</h2>
    <ul>
        <?php foreach ($items as $item): ?>
            <li>
                <a href="view_item.php?item_id=<?= $item['item_id'] ?>"><?= $item['name'] ?></a> - $<?= number_format($item['price'], 2) ?>
            </li>
        <?php endforeach; ?>
    </ul>


<?php include 'components/footer.php'; ?>
