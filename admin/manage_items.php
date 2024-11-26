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
<?php include 'components/header.php'; ?>

<div class="container my-5">
    <h1 class="text-center mb-4">Manage Items</h1>

    <!-- Add New Item Button -->
    <div class="text-end mb-3">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
            Add New Item
        </button>
    </div>

    <!-- Add New Item Modal -->
    <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="manage_items.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add New Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Item Name</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="price" class="form-label">Price</label>
                            <input type="number" name="price" id="price" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" name="quantity" id="quantity" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="category_id" class="form-label">Category</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <?php
                                $categoryQuery = "SELECT * FROM categories";
                                $categoryStmt = $pdo->query($categoryQuery);
                                while ($category = $categoryStmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="shop_id" class="form-label">Shop</label>
                            <select name="shop_id" id="shop_id" class="form-select" required>
                                <?php
                                $shopQuery = "SELECT * FROM shops";
                                $shopStmt = $pdo->query($shopQuery);
                                while ($shop = $shopStmt->fetch(PDO::FETCH_ASSOC)) {
                                    echo "<option value='{$shop['shop_id']}'>{$shop['shop_name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="add_item" class="btn btn-primary">Add Item</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Items List -->
    <h2 class="mb-4">Items List</h2>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-dark">
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
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['description']); ?></td>
                        <td>₱<?= number_format($item['price'], 2); ?></td>
                        <td><?= htmlspecialchars($item['quantity']); ?></td>
                        <td><?= htmlspecialchars($item['category_name']); ?></td>
                        <td><?= htmlspecialchars($item['shop_name']); ?></td>
                        <td>
                            <a href="edit_item.php?item_id=<?= $item['item_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="manage_items.php?delete_item=<?= $item['item_id']; ?>"
                                onclick="return confirm('Are you sure you want to delete this item?')"
                                class="btn btn-danger btn-sm">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'components/footer.php'; ?>