<?php
session_start();
if (!isset($_SESSION["Active"]) || $_SESSION["Active"] !== 1) {
    header("location: login.php");
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connect = new mysqli('127.0.0.1', $_ENV['SELECTUSER'], $_ENV['SELECTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . htmlspecialchars($connect->connect_error));
}

// gets user devicces
$userId = intval($_SESSION['UserID']);
$sql = "SELECT i.device, i.issuedate, i.expirydate, a.name AS device_name, a.type, a.brand, a.model
        FROM issued i
        JOIN asset a ON i.device = a.assetno
        WHERE i.user = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Devices</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <li><a href="home.php">Home</a></li>
        <li><a href="devices.php">Devices</a></li>
        <li><a href="my_devices.php" class="active">My Devices</a></li>
        <li class="logout"><a href="logout.php">Logout</a></li>
        <li class="account"><a href="account.php">Account</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>My Devices</h1>
        <?php if (!empty($devices)): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Type</th>
                        <th>Brand</th>
                        <th>Model</th>
                        <th>Issue Date</th>
                        <th>Expiry Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['device_name']) ?></td>
                            <td><?= htmlspecialchars($device['type']) ?></td>
                            <td><?= htmlspecialchars($device['brand']) ?></td>
                            <td><?= htmlspecialchars($device['model']) ?></td>
                            <td><?= htmlspecialchars(date('F j, Y', strtotime($device['issuedate']))) ?></td>
                            <td><?= htmlspecialchars(date('F j, Y', strtotime($device['expirydate']))) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No devices issued to you.</p>
        <?php endif; ?>
    </div>
</body>
</html>