<?php
session_start();
require_once '../config/database.php';

// Check if the user is an admin
if ($_SESSION['role'] != 'admin') {
    header("Location: index.php"); // Redirect to homepage if not an admin
    exit();
}

// Fetch all items from the database
$query = "SELECT items.item_id, items.name, items.description, items.price, items.quantity, categories.category_name, shops.shop_name 
          FROM items
          JOIN categories ON items.category_id = categories.category_id
          JOIN shops ON items.shop_id = shops.shop_id";
$stmt = $pdo->query($query);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Add new item
if (isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $category_id = $_POST['category_id'];
    $shop_id = $_POST['shop_id'];

    $insertQuery = "INSERT INTO items (name, description, price, quantity, category_id, shop_id) 
                    VALUES (:name, :description, :price, :quantity, :category_id, :shop_id)";
    $stmt = $pdo->prepare($insertQuery);
    $stmt->execute([
        ':name' => $name,
        ':description' => $description,
        ':price' => $price,
        ':quantity' => $quantity,
        ':category_id' => $category_id,
        ':shop_id' => $shop_id,
    ]);

    header("Location: manage_items.php"); // Reload the page after adding the item
    exit();
}

// Delete item
if (isset($_GET['delete_item'])) {
    $item_id = $_GET['delete_item'];

    $deleteQuery = "DELETE FROM items WHERE item_id = :item_id";
    $stmt = $pdo->prepare($deleteQuery);
    $stmt->execute([':item_id' => $item_id]);

    header("Location: manage_items.php"); // Reload the page after deletion
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
</head>
<body>
    <h1>Manage Items</h1>
    
    <!-- Add New Item Form -->
    <h2>Add New Item</h2>
    <form action="manage_items.php" method="POST">
        <label for="name">Item Name:</label>
        <input type="text" name="name" id="name" required><br>

        <label for="description">Description:</label>
        <textarea name="description" id="description" required></textarea><br>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" step="0.01" required><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" id="quantity" required><br>

        <label for="category_id">Category:</label>
        <select name="category_id" id="category_id" required>
            <?php
            // Fetch categories for the dropdown
            $categoryQuery = "SELECT * FROM categories";
            $categoryStmt = $pdo->query($categoryQuery);
            while ($category = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
            }
            ?>
        </select><br>

        <label for="shop_id">Shop:</label>
        <select name="shop_id" id="shop_id" required>
            <?php
            // Fetch shops for the dropdown
            $shopQuery = "SELECT * FROM shops";
            $shopStmt = $pdo->query($shopQuery);
            while ($shop = $shopStmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<option value='{$shop['shop_id']}'>{$shop['shop_name']}</option>";
            }
            ?>
        </select><br>

        <button type="submit" name="add_item">Add Item</button>
    </form>

    <!-- Items List -->
    <h2>Items List</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Item Name</th>
                <th>Description</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Category</th>
                <th>Shop</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo htmlspecialchars($item['description']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                    <td><?php echo htmlspecialchars($item['shop_name']); ?></td>
                    <td>
                        <a href="edit_item.php?item_id=<?php echo $item['item_id']; ?>">Edit</a> |
                        <a href="manage_items.php?delete_item=<?php echo $item['item_id']; ?>" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
