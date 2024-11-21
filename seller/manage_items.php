<?php
session_start();
require_once '../config/database.php';

// Check if the user is logged in and is a seller
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

// Fetch seller's shop details
$query = "SELECT * FROM shops WHERE user_id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$seller_id]);
$shop = $stmt->fetch();

// Fetch items listed by the seller
$query_items = "SELECT * FROM items WHERE shop_id = ?";
$stmt_items = $pdo->prepare($query_items);
$stmt_items->execute([$shop['shop_id']]);
$items = $stmt_items->fetchAll();

// Handle delete item action
if (isset($_GET['delete_item_id'])) {
    $item_id = $_GET['delete_item_id'];

    // Delete the item from the database
    $delete_query = "DELETE FROM items WHERE item_id = ?";
    $stmt_delete = $pdo->prepare($delete_query);
    $stmt_delete->execute([$item_id]);

    // Redirect back to the manage items page after deletion
    header("Location: manage_items.php");
    exit;
}
?>

<?php include 'components/header.php'; ?>


<main class="dashboard-content">
    <h1>Manage Your Items, <?= htmlspecialchars($shop['shop_name']) ?></h1>

    <!-- Items List Section -->
    <div class="dashboard-section">
        <h2>Your Items</h2>
        <a href="add_item.php" class="btn">add new item</a>
        <ul>
            <?php if ($items): ?>
                <?php foreach ($items as $item): ?>
                    <li>
                        <?php if ($item['image']): ?>
                            <img src="../uploads/items/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="100" height="100">
                        <?php else: ?>
                            <p>No image</p>
                        <?php endif; ?>
                        <strong><?= htmlspecialchars($item['name']) ?></strong><br>
                        Price: $<?= number_format($item['price'], 2) ?><br>
                        Quantity: <?= $item['quantity'] ?><br>
                        Condition: <?= ucfirst($item['condition']) ?><br>
                        <div class="action-buttons">
                            <a class="btn" href="edit_item.php?item_id=<?= $item['item_id'] ?>">Edit</a>
                            <a class="btn" href="manage_items.php?delete_item_id=<?= $item['item_id'] ?>" class="delete-button" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li>No items listed yet.</li>
            <?php endif; ?>
        </ul>
    </div>


</main>

<?php include 'components/footer.php'; ?>

</body>

</html>