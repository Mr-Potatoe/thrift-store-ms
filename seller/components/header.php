<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($shop['shop_name']) ?> Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    <link rel="icon" href="favicon.ico">
</head>

<body>

    <header>
        <div class="logo">
            <h1><?= htmlspecialchars($shop['shop_name']) ?></h1>
        </div>
        <nav>
            <ul>
                <li><a href="dashboard.php">Home</a></li>
                <li><a href="manage_items.php">Manage Items</a></li>
                <li><a href="manage_orders.php">Manage Orders</a></li>
                <li><a href="manage_feedback.php">Manage Feedback</a></li>
                <li><a href="../logout.php">Logout</a></li>
            </ul>
        </nav>
    </header>