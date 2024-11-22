<?php
// Include database connection and session management
require_once '../config/database.php';
session_start();

// Add item
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $item_name = $_POST['item_name'];
    $item_description = $_POST['item_description'];
    $item_price = $_POST['item_price'];
    $item_quantity = $_POST['item_quantity'];
    $query = "INSERT INTO items (name, description, price, quantity) VALUES (:name, :description, :price, :quantity)";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['name' => $item_name, 'description' => $item_description, 'price' => $item_price, 'quantity' => $item_quantity]);
}

// Fetch items
$query = "SELECT * FROM items";
$stmt = $pdo->query($query);
?>
<?php include 'components/header.php'; ?>
<h1>Item Management</h1>

<!-- Add item form -->
<form method="POST">
    <input type="text" name="item_name" placeholder="Item Name" required>
    <textarea name="item_description" placeholder="Description"></textarea>
    <input type="number" name="item_price" placeholder="Price" required>
    <input type="number" name="item_quantity" placeholder="Quantity" required>
    <button type="submit" name="add_item">Add Item</button>
</form>

<!-- Items list -->
<table>
    <thead>
        <tr>
            <th>Item Name</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($item = $stmt->fetch()): ?>
        <tr>
            <td><?= $item['name'] ?></td>
            <td><?= $item['price'] ?></td>
            <td><?= $item['quantity'] ?></td>
            <td>
                <a href="items.php?edit=<?= $item['item_id'] ?>">Edit</a> |
                <a href="items.php?delete=<?= $item['item_id'] ?>">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
<?php include 'components/footer.php'; ?>
