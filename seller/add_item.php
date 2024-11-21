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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $condition = $_POST['condition'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $auction = isset($_POST['auction']) ? 1 : 0;
    $auction_end_time = $_POST['auction_end_time'] ? $_POST['auction_end_time'] : NULL;
    $category_id = $_POST['category_id'];

    if (!isset($error)) {
        try {
            // Insert item into the `items` table
            $query_item = "INSERT INTO items (shop_id, category_id, name, description, `condition`, price, auction, auction_end_time, quantity) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_item = $pdo->prepare($query_item);
            $stmt_item->execute([$shop['shop_id'], $category_id, $name, $description, $condition, $price, $auction, $auction_end_time, $quantity]);

            // Get the last inserted item ID
            $item_id = $pdo->lastInsertId();

            // Handle multiple image uploads
            if (isset($_FILES['images']) && count($_FILES['images']['name']) > 0) {
                $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
                foreach ($_FILES['images']['name'] as $index => $file_name) {
                    $file_tmp = $_FILES['images']['tmp_name'][$index];
                    $file_size = $_FILES['images']['size'][$index];
                    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                    if (in_array($file_ext, $allowed_exts) && $file_size <= 5000000) { // Max 5MB
                        // Generate a unique filename
                        $image_name = uniqid('item_', true) . '.' . $file_ext;
                        $image_path = '../uploads/items/' . $image_name;

                        // Move the uploaded file to the uploads directory
                        if (move_uploaded_file($file_tmp, $image_path)) {
                            // Insert the image into the `item_images` table
                            $query_image = "INSERT INTO item_images (item_id, image_url) VALUES (?, ?)";
                            $stmt_image = $pdo->prepare($query_image);
                            $stmt_image->execute([$item_id, $image_name]);
                        } else {
                            $error = "Error uploading image: $file_name";
                        }
                    } else {
                        $error = "Invalid image file or file size too large: $file_name";
                    }
                }
            }

            // Redirect after successful item addition
            if (!isset($error)) {
                header("Location: dashboard.php");
                exit;
            }
        } catch (PDOException $e) {
            $error = "Error: " . $e->getMessage();
        }
    }
}


// Fetch categories for the seller to select
$query_categories = "SELECT * FROM categories";
$stmt_categories = $pdo->prepare($query_categories);
$stmt_categories->execute();
$categories = $stmt_categories->fetchAll();
?>




<?php include 'components/header.php'; ?>

<?php if (isset($error)) {
    echo "<p style='color: red;'>$error</p>";
} ?>

<main class="dashboard-content">
    <h1>Add New Item</h1>
    <section class="dashboard-section">
        <h2>Item Information</h2>
        <form method="POST" action="add_item.php" enctype="multipart/form-data" class="item-form">
            <div class="form-group">
                <label for="name">Item Name:</label>
                <input type="text" name="name" id="name" required placeholder="Enter item name">
            </div>

            <div class="form-group">
                <label for="description">Description:</label>
                <textarea name="description" id="description" required placeholder="Enter item description"></textarea>
            </div>

            <div class="form-group">
                <label for="condition">Condition:</label>
                <select name="condition" id="condition" required>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                </select>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" name="price" id="price" step="0.01" required placeholder="Enter price">
            </div>

            <div class="form-group">
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" required placeholder="Enter quantity">
            </div>

            <div class="form-group">
                <label for="auction">Auction:</label>
                <input type="checkbox" name="auction" id="auction"> Enable Auction
            </div>

            <div class="form-group">
                <label for="auction_end_time">Auction End Time:</label>
                <input type="datetime-local" name="auction_end_time" id="auction_end_time">
            </div>

            <div class="form-group">
                <label for="category_id">Category:</label>
                <select name="category_id" id="category_id" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="images">Item Images:</label>
                <input type="file" name="images[]" id="images" accept="image/*" multiple>
            </div>

            <button type="submit" class="btn">Add Item</button>
        </form>
    </section>
</main>

<?php include 'components/footer.php'; ?>

