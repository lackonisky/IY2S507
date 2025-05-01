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

// Fetch RSS links securely
$sql = "SELECT links FROM rss";
$stmt = $connect->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$feeds = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="user_management.php">Account Requests</a></li>
            <li><a href="device_requests.php">Device Requests</a></li>
            <li><a href="manage_devices.php">Manage Devices</a></li>
            <li><a href="add_device.php">Add Devices</a></li>
            <li><a href="rss_management.php">RSS Management</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>
    <div class="content">
        <h1>Admin RSS Feeds</h1>
        <h2>RSS Feeds</h2
        <div class="rss-container">
            <?php foreach ($feeds as $feed): ?>
                <?php
                $rss = @simplexml_load_file($feed['links']);
                if ($rss === false) {
                    echo "<p>Unable to load feed content.</p>";
                    continue;
                }
                foreach ($rss->channel->item as $item): ?>
                    <div class="rss-item">
                        <h4><?= htmlspecialchars($item->title) ?></h4>
                        <?php
                        $image = '';
                        if (isset($item->enclosure['url'])) {
                            $image = htmlspecialchars($item->enclosure['url']);
                        } elseif (isset($item->children('media', true)->content['url'])) {
                            $image = htmlspecialchars($item->children('media', true)->content['url']);
                        }
                        ?>
                        <?php if ($image): ?>
                            <img src="<?= $image ?>" alt="Feed Image" class="rss-image">
                        <?php else: ?>
                            <img src="placeholder.jpg" alt="No Image Available" class="rss-image">
                        <?php endif; ?>
                        <p><?= htmlspecialchars($item->description) ?></p>
                        <a href="<?= htmlspecialchars($item->link) ?>" target="_blank">Read more</a>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>