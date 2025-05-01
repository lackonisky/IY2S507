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

// add or remove rss from form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['rss-link'])) {
        // Add RSS link
        $rssLink = trim($_POST['rss-link']);
        $stmt = $connect->prepare("INSERT INTO rss (links) VALUES (?)");
        $stmt->bind_param("s", $rssLink);
        $stmt->execute();
        $stmt->close();
    } elseif (isset($_POST['delete-link'])) {
        // Delete RSS link
        $deleteLink = trim($_POST['delete-link']);
        $stmt = $connect->prepare("DELETE FROM rss WHERE links = ?");
        $stmt->bind_param("s", $deleteLink);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch RSS links
$sql = "SELECT links FROM rss";
$result = $connect->query($sql);
$feeds = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RSS Management</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="user_management.php">Account Requests</a></li>
            <li><a href="device_requests.php">Device Requests</a></li>
            <li><a href="manage_devices.php">Manage Devices</a></li>
            <li><a href="add_device.php">Add Devices</a></li>
            <li><a href="rss_management.php" class="active">RSS Management</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="container">
        <h1>RSS Management</h1>
        <div class="add-container" style="margin-bottom: 20px; padding: 15px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);">
            <h2>Add RSS Link</h2>
            <form method="POST" class="filter-form">
                <div class="filter-group" style="display: flex; align-items: center; gap: 10px;">
                    <label for="rss-link">RSS Link:</label>
                    <input type="url" id="rss-link" name="rss-link" required>
                    <input type="submit" class="btn" value="Add RSS">
                </div>
            </form>
        </div>
        <table class="user-table">
            <thead>
                <tr>
                    <th>RSS Link</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($feeds)): ?>
                    <?php foreach ($feeds as $feed): ?>
                        <tr>
                            <td><?= htmlspecialchars($feed['links']) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="delete-link" value="<?= htmlspecialchars($feed['links']) ?>">
                                    <button type="submit" class="btn" onclick="return confirm('Are you sure?');">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="2" class="no-data">No RSS feeds found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>