<?php
session_start();
if (!isset($_SESSION["Active"]) || $_SESSION["Active"] !== 1) {
    header("location: login.php");
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

// Fetch all devices
$sql = "SELECT * FROM asset";
$result = $connect->query($sql);
$devices = $result->fetch_all(MYSQLI_ASSOC);

// Handle device request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $device = $_POST['device'];
    $length = $_POST['length'];
    $reason = $_POST['reason'];
    $userId = $_SESSION['UserID'];

    $requestSql = "INSERT INTO requests (user, device, length, reason, approval) VALUES (?, ?, ?, ?, 'Pending')";
    $stmt = $connect->prepare($requestSql);
    $stmt->bind_param("iiss", $userId, $device, $length, $reason);
    if ($stmt->execute()) {
        echo "<p>Request submitted successfully!</p>";
    } else {
        echo "<p>Error submitting request: " . $stmt->error . "</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devices</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<nav class="navbar">
    <ul class="nav-items">
        <li><a href="home.php">Home</a></li>
        <li><a href="devices.php">Devices</a></li>
        <li><a href="my_devices.php">My Devices</a></li>
    </ul>
    <form method="post" action="logout.php" class="logout-form">
        <button type="submit">Logout</button>
    </form>
</nav>
    <div class="content">
        <h1>Available Devices</h1>
        <?php if (!empty($devices)): ?>
            <table border="1" cellpadding="10" cellspacing="0">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Available</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['name']) ?></td>
                            <td><?= htmlspecialchars($device['type']) ?></td>
                            <td><?= htmlspecialchars($device['brand']) ?></td>
                            <td><?= htmlspecialchars($device['model']) ?></td>
                            <td><?= htmlspecialchars($device['available'] ? 'Yes' : 'No') ?></td>
                            <td>
                                <?php if ($device['available']): ?>
                                    <form method="post">
                                        <input type="hidden" name="device" value="<?= htmlspecialchars($device['assetno']) ?>">
                                        <label for="length">Length (days):</label>
                                        <input type="number" name="length" id="length" required>
                                        <label for="reason">Reason:</label>
                                        <input type="text" name="reason" id="reason" required>
                                        <input type="submit" value="Request">
                                    </form>
                                <?php else: ?>
                                    <p>Not Available</p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No devices available.</p>
        <?php endif; ?>
    </div>
</body>
</html>

