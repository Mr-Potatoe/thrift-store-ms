 
<?php
// config/database.php

// Database credentials
$host = 'localhost';  // Change to your database host
$dbname = 'tel2';  // The name of your database
$username = 'root';  // Database username
$password = '';  // Database password (replace with your actual password)
$port = 3306;

// Set DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8";

// Set options for PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions for errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Fetch results as associative arrays
    PDO::ATTR_EMULATE_PREPARES => false, // Disable emulation of prepared statements
];

// Create PDO instance
try {
    $pdo = new PDO($dsn, $username, $password, $options);
    // Optionally, you can verify the connection (e.g., echo 'Connected successfully')
} catch (PDOException $e) {
    // Handle connection errors
    die("Database connection failed: " . $e->getMessage());
}
?>
