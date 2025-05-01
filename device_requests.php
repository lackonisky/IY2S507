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



// Handle approval or denial
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $connect = new mysqli('127.0.0.1', $_ENV['INSERTUSER'], $_ENV['INSERTPASS'], $_ENV['DATABASE']);
    if ($connect->connect_error) {
        die("Connection failed: " . htmlspecialchars($connect->connect_error));
    }
    $requestId = intval($_POST['request_id']);
    if (isset($_POST['approve'])) {
        $user = intval($_POST['user']);
        $device = intval($_POST['device']);
        $length = intval($_POST['length']);
        $issuedate = date('Y-m-d');
        $expirydate = date('Y-m-d', strtotime("+$length days"));

        // Insert into issued table
        $stmt = $connect->prepare("INSERT INTO issued (user, device, issuedate, expirydate) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user, $device, $issuedate, $expirydate);
        $stmt->execute();
        $stmt->close();
        $stmt = $connect->prepare("UPDATE asset SET available = 0 WHERE assetno = ?");
        $stmt->bind_param("i", $request['assetno']);
        $stmt->execute();
        $stmt->close();
    $connect->close();
    }
    $connect = new mysqli('127.0.0.1', $_ENV['DELETEUSER'], $_ENV['DELETEPASS'], $_ENV['DATABASE']);
    if ($connect->connect_error) {
        die("Connection failed: " . htmlspecialchars($connect->connect_error));
    }
    // Delete the request
    $stmt = $connect->prepare("DELETE FROM requests WHERE requestno = ?");
    $stmt->bind_param("i", $requestId);
    $stmt->execute();
    $stmt->close();
    $connect->close();
}
$connect = new mysqli('127.0.0.1', $_ENV['SELECTUSER'], $_ENV['SELECTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . htmlspecialchars($connect->connect_error));
}
// Fetch pending requests
$sql = "SELECT r.requestno, r.user, r.device, r.length, r.reason, u.firstname, u.lastname, d.name AS device_name 
        FROM requests r 
        JOIN users u ON r.user = u.uid 
        JOIN asset d ON r.device = d.assetno 
        WHERE r.approval = 'Pending'";
$result = $connect->query($sql);
$requests = $result->fetch_all(MYSQLI_ASSOC);
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Requests</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="user_management.php">Account Requests</a></li>
            <li><a href="device_requests.php" class="active">Device Requests</a></li>
            <li><a href="manage_devices.php">Manage Devices</a></li>
            <li><a href="add_device.php">Add Devices</a></li>
            <li><a href="rss_management.php">RSS Management</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>Review User Requests</h1>
        <?php if (!empty($requests)): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Device</th>
                        <th>Length (Days)</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= htmlspecialchars($request['firstname'] . ' ' . $request['lastname']) ?></td>
                            <td><?= htmlspecialchars($request['device_name']) ?></td>
                            <td><?= htmlspecialchars($request['length']) ?></td>
                            <td><?= htmlspecialchars($request['reason']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['requestno']) ?>">
                                    <input type="hidden" name="user" value="<?= htmlspecialchars($request['user']) ?>">
                                    <input type="hidden" name="device" value="<?= htmlspecialchars($request['device']) ?>">
                                    <input type="hidden" name="length" value="<?= htmlspecialchars($request['length']) ?>">
                                    <button type="submit" name="approve" class="btn">Approve</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?= htmlspecialchars($request['requestno']) ?>">
                                    <button type="submit" name="deny" class="btn">Deny</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No pending requests.</p>
        <?php endif; ?>
    </div>
</body>
</html>