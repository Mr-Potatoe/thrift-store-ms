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

// Get item ID from the query parameter
$item_id = $_GET['item_id'] ?? null;
if (!$item_id) {
    header("Location: dashboard.php");
    exit;
}

// Fetch item details from the database
$query_item = "SELECT * FROM items WHERE item_id = ? AND shop_id = ?";
$stmt_item = $pdo->prepare($query_item);
$stmt_item->execute([$item_id, $shop['shop_id']]);
$item = $stmt_item->fetch();

if (!$item) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the updated item details from the form
    $name = $_POST['name'];
    $description = $_POST['description'];
    $condition = $_POST['condition'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $auction = isset($_POST['auction']) ? 1 : 0;
    $auction_end_time = $_POST['auction_end_time'] ? $_POST['auction_end_time'] : NULL;
    $category_id = $_POST['category_id'];
    
    // Handle file upload (image)
    $image = $item['image']; // Default to the existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        // Upload new image
        $image = time() . "_" . $_FILES['image']['name'];
        $upload_path = "../uploads/items/" . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $upload_path);
    }

    // Update item in the database
    try {
        $query_update = "UPDATE items SET name = ?, description = ?, `condition` = ?, price = ?, quantity = ?, auction = ?, auction_end_time = ?, category_id = ?, image = ? WHERE item_id = ? AND shop_id = ?";
        $stmt_update = $pdo->prepare($query_update);
        $stmt_update->execute([$name, $description, $condition, $price, $quantity, $auction, $auction_end_time, $category_id, $image, $item_id, $shop['shop_id']]);
        
        // Redirect to the dashboard after successful update
        header("Location: dashboard.php");
        exit;
    } catch (PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch categories for the form dropdown
$query_categories = "SELECT * FROM categories";
$stmt_categories = $pdo->prepare($query_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();
?>

<?php include 'components/header.php'; ?>
<main class="dashboard-content">
<section class="dashboard-section">
    <h1>Edit Item: <?= htmlspecialchars($item['name']) ?></h1>

    <?php if (isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>

    <form method="POST" action="edit_item.php?item_id=<?= $item['item_id'] ?>" enctype="multipart/form-data">
        <label for="name">Item Name:</label><br>
        <input type="text" name="name" value="<?= htmlspecialchars($item['name']) ?>" required><br>

        <label for="description">Description:</label><br>
        <textarea name="description" required><?= htmlspecialchars($item['description']) ?></textarea><br>

        <label for="condition">Condition:</label><br>
        <select name="condition" required>
            <option value="excellent" <?= $item['condition'] == 'excellent' ? 'selected' : '' ?>>Excellent</option>
            <option value="good" <?= $item['condition'] == 'good' ? 'selected' : '' ?>>Good</option>
            <option value="fair" <?= $item['condition'] == 'fair' ? 'selected' : '' ?>>Fair</option>
        </select><br>

        <label for="price">Price:</label><br>
        <input type="number" name="price" value="<?= $item['price'] ?>" step="0.01" required><br>

        <label for="quantity">Quantity:</label><br>
        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" required><br>

        <label for="auction">Auction:</label>
        <input type="checkbox" name="auction" <?= $item['auction'] ? 'checked' : '' ?>><br>

        <label for="auction_end_time">Auction End Time:</label><br>
        <input type="datetime-local" name="auction_end_time" value="<?= $item['auction_end_time'] ?>"><br>

        <label for="category_id">Category:</label><br>
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $item['category_id'] ? 'selected' : '' ?>><?= htmlspecialchars($category['category_name']) ?></option>
            <?php endforeach; ?>
        </select><br>

        <label for="image">Image:</label><br>
        <?php if ($item['image']): ?>
            <img src="../uploads/items/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" width="100" height="100"><br>
        <?php endif; ?>
        <input type="file" name="image"><br>

        <button type="submit">Save Changes</button>
    </form>

    <a href="dashboard.php" class="cancel-btn">Cancel</a>
</section>
</main>
<?php include 'components/footer.php'; ?>
