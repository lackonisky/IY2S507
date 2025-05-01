<?php
session_start();
if (!isset($_SESSION["Active"]) || $_SESSION["Active"] !== 1 || $_SESSION["access"] != 1) {
    header("location: home.php");
    exit();
}

// Database connection
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connect = new mysqli('127.0.0.1', $_ENV['SELECTUSER'], $_ENV['SELECTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . htmlspecialchars($connect->connect_error));
}

// Handle form submission for editing a device
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit-device'])) {
    $assetno = intval($_POST['assetno']);
    $type = trim($_POST['type']);
    $name = trim($_POST['name']);
    $model = trim($_POST['model']);
    $serial = trim($_POST['serial']);
    $brand = trim($_POST['brand']);
    $cpu = trim($_POST['cpu']);
    $ram = trim($_POST['ram']);
    $storage = trim($_POST['storage']);
    $available = isset($_POST['available']) ? 1 : 0;

    $stmt = $connect->prepare("UPDATE asset SET type = ?, name = ?, model = ?, serial = ?, brand = ?, cpu = ?, ram = ?, storage = ?, available = ? WHERE assetno = ?");
    $stmt->bind_param("ssssssssii", $type, $name, $model, $serial, $brand, $cpu, $ram, $storage, $available, $assetno);
    $stmt->execute();
    $stmt->close();
}

// Fetch devices
$sql = "SELECT assetno, type, name, model, serial, brand, cpu, ram, storage, available FROM asset";
$result = $connect->query($sql);
$devices = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Devices</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="user_management.php">Account Requests</a></li>
            <li><a href="device_requests.php">Device Requests</a></li>
            <li><a href="manage_devices.php" class="active">Manage Devices</a></li>
            <li><a href="add_device.php">Add Devices</a></li>
            <li><a href="rss_management.php">RSS Management</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Manage Devices</h1>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Asset No</th>
                    <th>Type</th>
                    <th>Name</th>
                    <th>Model</th>
                    <th>Serial</th>
                    <th>Brand</th>
                    <th>CPU</th>
                    <th>RAM</th>
                    <th>Storage</th>
                    <th>Available</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($devices)): ?>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <form method="POST">
                                <td><?= htmlspecialchars($device['assetno']) ?></td>
                                <td><input type="text" name="type" value="<?= htmlspecialchars($device['type']) ?>" required></td>
                                <td><input type="text" name="name" value="<?= htmlspecialchars($device['name']) ?>" required></td>
                                <td><input type="text" name="model" value="<?= htmlspecialchars($device['model']) ?>" required></td>
                                <td><input type="text" name="serial" value="<?= htmlspecialchars($device['serial']) ?>" required></td>
                                <td><input type="text" name="brand" value="<?= htmlspecialchars($device['brand']) ?>" required></td>
                                <td><input type="text" name="cpu" value="<?= htmlspecialchars($device['cpu']) ?>"></td>
                                <td><input type="text" name="ram" value="<?= htmlspecialchars($device['ram']) ?>"></td>
                                <td><input type="text" name="storage" value="<?= htmlspecialchars($device['storage']) ?>"></td>
                                <td>
                                    <input type="checkbox" name="available" <?= $device['available'] ? 'checked' : '' ?>>
                                </td>
                                <td>
                                    <input type="hidden" name="assetno" value="<?= htmlspecialchars($device['assetno']) ?>">
                                    <button type="submit" name="edit-device" class="btn">Save</button>
                                </td>
                            </form>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" class="no-data">No devices found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>