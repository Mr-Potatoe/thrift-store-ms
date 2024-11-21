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

    // Handle file upload
    $image_name = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        // Get file info
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name = $_FILES['image']['name'];
        $file_size = $_FILES['image']['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Check if the file is an image
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_ext, $allowed_exts) && $file_size <= 5000000) { // max 5MB
            // Generate a unique filename
            $image_name = uniqid('item_', true) . '.' . $file_ext;
            $image_path = '../uploads/items/' . $image_name;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($file_tmp, $image_path)) {
                // Image uploaded successfully
            } else {
                $error = "Error uploading image.";
            }
        } else {
            $error = "Invalid image file or file size is too large.";
        }
    }

    // Insert item into the database
    if (!isset($error)) {
        try {
            $query_item = "INSERT INTO items (shop_id, category_id, name, description, `condition`, price, auction, auction_end_time, quantity, image) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt_item = $pdo->prepare($query_item);
            $stmt_item->execute([$shop['shop_id'], $category_id, $name, $description, $condition, $price, $auction, $auction_end_time, $quantity, $image_name]);

            // Redirect after successful item addition
            header("Location: dashboard.php");
            exit;
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
    <h1>Add New Item</h1>

    <?php if (isset($error)) { echo "<p style='color: red;'>$error</p>"; } ?>

    <form method="POST" action="add_item.php" enctype="multipart/form-data">
        <label for="name">Item Name:</label><br>
        <input type="text" name="name" required><br>

        <label for="description">Description:</label><br>
        <textarea name="description" required></textarea><br>

        <label for="condition">Condition:</label><br>
        <select name="condition" required>
            <option value="excellent">Excellent</option>
            <option value="good">Good</option>
            <option value="fair">Fair</option>
        </select><br>

        <label for="price">Price:</label><br>
        <input type="number" name="price" step="0.01" required><br>

        <label for="quantity">Quantity:</label><br>
        <input type="number" name="quantity" required><br>

        <label for="auction">Auction:</label>
        <input type="checkbox" name="auction"><br>

        <label for="auction_end_time">Auction End Time:</label><br>
        <input type="datetime-local" name="auction_end_time"><br>

        <label for="category_id">Category:</label><br>
        <select name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['category_id'] ?>"><?= $category['category_name'] ?></option>
            <?php endforeach; ?>
        </select><br>

        <!-- Item Image Upload -->
        <label for="image">Item Image:</label><br>
        <input type="file" name="image" accept="image/*"><br><br>

        <button type="submit">Add Item</button>
    </form>
<?php include 'components/footer.php'; ?>
