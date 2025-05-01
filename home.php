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
    die("Connection failed: " . $connect->connect_error);
}

//get rss links
$sql = "SELECT links FROM rss";
$result = $connect->query($sql);
$feeds = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="home.php" class="active">Home</a></li>
            <li><a href="devices.php">Devices</a></li>
            <li><a href="my_devices.php">My Devices</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
            <li class="account"><a href="account.php">Account</a></li>
        </ul>
    </nav>
    <div class="content">
        <h1>News</h1>
        <div class="rss-container">
            <h2>RSS Feeds</h2>
            //htmlspecialchars to prevent XSS and display rss content, goes through each feed and prints
            <div class="rss-grid">
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
                            //gets image if present in rss
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
    </div>
</body>
</html>