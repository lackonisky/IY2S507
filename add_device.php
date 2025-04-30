<?php
session_start();
if (!isset($_SESSION["Active"]) || $_SESSION["Active"] !== 1) {
    header("location: login.php");
    exit();
}

if ($_SESSION['access'] != 1) {
    echo "<p>Access denied. You do not have permission to access this page.</p>";
    exit();
}

// Database connection
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connect = new mysqli('127.0.0.1', $_ENV['SELECTUSER'], $_ENV['SELECTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $name = $_POST['name'];
    $image = $_POST['image'];
    $model = $_POST['model'];
    $serial = $_POST['serial'];
    $brand = $_POST['brand'];
    $cpu = $_POST['cpu'];
    $ram = $_POST['ram'];
    $storage = $_POST['storage'];
    $available = isset($_POST['available']) ? 1 : 0;

    $sql = "INSERT INTO asset (type, name, image, model, serial, brand, cpu, ram, storage, available)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("ssssssssii", $type, $name, $image, $model, $serial, $brand, $cpu, $ram, $storage, $available);

    if ($stmt->execute()) {
        echo "<p>Device added successfully!</p>";
    } else {
        echo "<p>Error adding device: " . $stmt->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Device</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
    <nav class="navbar">
        <ul>
            <li><a href="home.php">Home</a></li>
            <li><a href="devices.php">Devices</a></li>
            <li><a href="my_devices.php">My Devices</a></li>
        </ul>
    </nav>
    <div class="content">
        <h1>Add a New Device</h1>
        <form method="post">
            <label for="type">Type:</label>
            <input type="text" id="type" name="type" required>

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="image">Image URL:</label>
            <input type="text" id="image" name="image">

            <label for="model">Model:</label>
            <input type="text" id="model" name="model" required>

            <label for="serial">Serial:</label>
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
            <input type="checkbox" id="available" name="available">

            <input type="submit" value="Add Device">
        </form>
    </div>
</body>
</html>
.