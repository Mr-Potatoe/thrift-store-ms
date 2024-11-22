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

// Fetch item images from the item_images table
$query_item_images = "SELECT * FROM item_images WHERE item_id = ?";
$stmt_item_images = $pdo->prepare($query_item_images);
$stmt_item_images->execute([$item_id]);
$item_images = $stmt_item_images->fetchAll();


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
    $category_id = $_POST['category_id'];

    // Handle image upload
    $image_files = $_FILES['images']['name'];
    if ($image_files) {
        foreach ($image_files as $key => $image_name) {
            if ($_FILES['images']['error'][$key] === 0) {
                $new_image_name = time() . "_" . $image_name;
                $upload_path = "../uploads/items/" . $new_image_name;
                move_uploaded_file($_FILES['images']['tmp_name'][$key], $upload_path);

                // Insert the image into the database
                $query_insert_image = "INSERT INTO item_images (item_id, image_url) VALUES (?, ?)";
                $stmt_insert_image = $pdo->prepare($query_insert_image);
                $stmt_insert_image->execute([$item_id, $new_image_name]);
            }
        }
    }

    // Update the main item in the database
    try {
        $query_update = "UPDATE items SET name = ?, description = ?, `condition` = ?, price = ?, quantity = ?, category_id = ? WHERE item_id = ? AND shop_id = ?";
        $stmt_update = $pdo->prepare($query_update);
        $stmt_update->execute([$name, $description, $condition, $price, $quantity,  $category_id, $item_id, $shop['shop_id']]);

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

<main class="dashboard-content container py-4">
    <section class="dashboard-section">
        <h1 class="text-primary mb-4">Edit Item: <?= htmlspecialchars($item['name']) ?></h1>

        <!-- Error Message -->
        <?php if (isset($error)): ?>
            <p class="alert alert-danger"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Cancel Button -->
        <a href="dashboard.php" class="btn btn-secondary mb-4">Cancel</a>

        <!-- Edit Item Form -->
        <form method="POST" action="edit_item.php?item_id=<?= $item['item_id'] ?>" enctype="multipart/form-data" class="item-form">

            <!-- Item Name -->
            <div class="mb-3">
                <label for="name" class="form-label">Item Name:</label>
                <input type="text" name="name" id="name" class="form-control" value="<?= htmlspecialchars($item['name']) ?>" required placeholder="Enter item name">
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea name="description" id="description" class="form-control" required rows="3" placeholder="Enter item description"><?= htmlspecialchars($item['description']) ?></textarea>
            </div>

            <!-- Condition -->
            <div class="mb-3">
                <label for="condition" class="form-label">Condition:</label>
                <select name="condition" id="condition" class="form-select" required>
                    <option value="excellent" <?= $item['condition'] == 'excellent' ? 'selected' : '' ?>>Excellent</option>
                    <option value="good" <?= $item['condition'] == 'good' ? 'selected' : '' ?>>Good</option>
                    <option value="fair" <?= $item['condition'] == 'fair' ? 'selected' : '' ?>>Fair</option>
                </select>
            </div>

            <!-- Price -->
            <div class="mb-3">
                <label for="price" class="form-label">Price:</label>
                <input type="number" name="price" id="price" class="form-control" value="<?= $item['price'] ?>" step="0.01" required placeholder="Enter price">
            </div>

            <!-- Quantity -->
            <div class="mb-3">
                <label for="quantity" class="form-label">Quantity:</label>
                <input type="number" name="quantity" id="quantity" class="form-control" value="<?= $item['quantity'] ?>" required placeholder="Enter quantity">
            </div>

            <!-- Category -->
            <div class="mb-3">
                <label for="category_id" class="form-label">Category:</label>
                <select name="category_id" id="category_id" class="form-select" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['category_id'] ?>" <?= $category['category_id'] == $item['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($category['category_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Item Images -->
            <div class="mb-3">
                <label for="images" class="form-label">Item Image(s):</label><br>
                <?php if ($item_images): ?>
                    <div class="mb-3">
                        <strong>Existing Images:</strong>
                        <div class="row">
                            <?php foreach ($item_images as $img): ?>
                                <div class="col-4 col-md-2 mb-3">
                                    <img src="../uploads/items/<?= htmlspecialchars($img['image_url']) ?>" alt="Item Image" class="img-fluid rounded">
                                    <a href="delete_image.php?image_id=<?= $img['image_id'] ?>&item_id=<?= $item['item_id'] ?>"
                                        class="btn btn-danger btn-sm mt-2 d-block"
                                        onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                <input type="file" name="images[]" id="images" class="form-control" accept="image/*" multiple>
            </div>

            <!-- Save Changes Button -->
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </section>
</main>


<?php include 'components/footer.php'; ?>