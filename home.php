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

// Fetch issued devices for the logged-in user
$userId = $_SESSION['UserID'];
$sql = "SELECT i.issueno, a.name, a.type, i.issuedate, i.expirydate 
        FROM issued i 
        JOIN asset a ON i.device = a.assetno 
        WHERE i.user = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$issuedDevices = $result->fetch_all(MYSQLI_ASSOC);
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
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
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
                            <td><?= $device['available'] ? 'Yes' : 'No' ?></td>
                            <td>
                                <?php if ($device['available']): ?>
                                    <form method="post">
                                        <input type="hidden" name="device" value="<?= $device['assetno'] ?>">
                                        <input type="number" name="length" placeholder="Days" required>
                                        <input type="text" name="reason" placeholder="Reason" required>
                                        <button type="submit">Request</button>
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