<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['access']) || $_SESSION['access'] != 1) {
    header("Location: home.php");
    exit();
}

// Database connection
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connect = new mysqli('127.0.0.1', $_ENV['INSERTUSER'], $_ENV['INSERTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $model = $_POST['model'];
    $serial = $_POST['serial'];
    $brand = $_POST['brand'];
    $cpu = $_POST['cpu'];
    $ram = $_POST['ram'];
    $storage = $_POST['storage'];
    $available = isset($_POST['available']) ? 1 : 0;

    $sql = "INSERT INTO asset (type, name, model, serial, brand, cpu, ram, storage, available) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssssssi", $type, $name, $model, $serial, $brand, $cpu, $ram, $storage, $available);

    if ($stmt->execute()) {
        $message = "Asset added successfully.";
    } else {
        $message = "Failed to add asset.";
    }
    $stmt->close();
}

$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Asset</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="user_management.php">Account Requests</a></li>
            <li><a href="device_requests.php">Device Requests</a></li>
            <li><a href="manage_devices.php">Manage Devices</a></li>
            <li><a href="add_device.php" class="active">Add Devices</a></li>
            <li><a href="rss_management.php">RSS Management</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Add Asset</h2>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <form method="POST">
            <label for="type">Device Type:</label>
            <select name="type" id="type" required>
                <option value="">Select Type</option>
                <option value="Laptop">Laptop</option>
                <option value="Phone">Phone</option>
                <option value="Tablet">Tablet</option>
            </select>

            <label for="name">Device Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="model">Model:</label>
            <input type="text" id="model" name="model" required>

            <label for="serial">Serial Number:</label>
            <input type="text" id="serial" name="serial" required>

            <label for="brand">Brand:</label>
            <input type="text" id="brand" name="brand" required>

            <label for="cpu">CPU:</label>
            <input type="text" id="cpu" name="cpu">

            <label for="ram">RAM:</label>
            <input type="text" id="ram" name="ram">

            <label for="storage">Storage:</label>
            <input type="text" id="storage" name="storage">

            <label for="available">Available:</label>
            <input type="checkbox" id="available" name="available" checked>

            <button type="submit" class="btn">Add Asset</button>
        </form>
    </div>
</body>
</html>