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

$connect = new mysqli('127.0.0.1', $_ENV['INSERTUSER'], $_ENV['INSERTPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Handle filtering and sorting
$filterType = $_GET['type'] ?? '';
$sortBy = $_GET['sort'] ?? 'name';
$sortOrder = $_GET['order'] ?? 'ASC';

$sql = "SELECT a.* 
        FROM asset a 
        WHERE a.available = 1 
        AND a.assetno NOT IN (
            SELECT i.device FROM issued i 
            UNION 
            SELECT r.device FROM requests r
        )";

if (!empty($filterType)) {
    $sql .= " AND a.type = ?";
}

$sql .= " ORDER BY $sortBy $sortOrder";

$stmt = $connect->prepare($sql);
if (!empty($filterType)) {
    $stmt->bind_param("s", $filterType);
}
$stmt->execute();
$result = $stmt->get_result();
$devices = $result->fetch_all(MYSQLI_ASSOC);

// Handle device request
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['device'])) {
    $device = $_POST['device'];
    $length = $_POST['length'];
    $reason = $_POST['reason'];
    $userId = $_SESSION['UserID'];

    $requestSql = "INSERT INTO requests (user, device, length, reason, approval) VALUES (?, ?, ?, ?, 'Pending')";
    $requestStmt = $connect->prepare($requestSql);
    $requestStmt->bind_param("iiis", $userId, $device, $length, $reason);

    if ($requestStmt->execute()) {
        $message = "Request submitted successfully.";
    } else {
        $error = "Error submitting request: " . $connect->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Devices</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="home.php">Home</a></li>
            <li><a href="devices.php" class="active">Devices</a></li>
            <li><a href="my_devices.php">My Devices</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h2>Available Devices</h2>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="filter-section">
            <form method="get" class="filter-form">
                <div class="filter-group">
                    <label for="type">Filter by Type:</label>
                    <select name="type" id="type">
                        <option value="">All</option>
                        <option value="Laptop" <?= $filterType === 'Laptop' ? 'selected' : '' ?>>Laptop</option>
                        <option value="Phone" <?= $filterType === 'Phone' ? 'selected' : '' ?>>Phone</option>
                        <option value="Tablet" <?= $filterType === 'Tablet' ? 'selected' : '' ?>>Tablet</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort">
                        <option value="name" <?= $sortBy === 'name' ? 'selected' : '' ?>>Name</option>
                        <option value="type" <?= $sortBy === 'type' ? 'selected' : '' ?>>Type</option>
                        <option value="brand" <?= $sortBy === 'brand' ? 'selected' : '' ?>>Brand</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="order">Order:</label>
                    <select name="order" id="order">
                        <option value="ASC" <?= $sortOrder === 'ASC' ? 'selected' : '' ?>>Ascending</option>
                        <option value="DESC" <?= $sortOrder === 'DESC' ? 'selected' : '' ?>>Descending</option>
                    </select>
                </div>
                <button type="submit" class="btn">Apply Filters</button>
            </form>
        </div>

        <?php if (!empty($devices)): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Model</th>
                        <th>Asset No</th>
                        <th>Serial</th>
                        <th>Brand</th>
                        <th>CPU</th>
                        <th>RAM</th>
                        <th>Storage</th>
                        <th>Request Device</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($devices as $device): ?>
                        <tr>
                            <td><?= htmlspecialchars($device['name']) ?></td>
                            <td><?= htmlspecialchars($device['type']) ?></td>
                            <td><?= htmlspecialchars($device['model']) ?></td>
                            <td><?= htmlspecialchars($device['assetno']) ?></td>
                            <td><?= htmlspecialchars($device['serial']) ?></td>
                            <td><?= htmlspecialchars($device['brand']) ?></td>
                            <td><?= htmlspecialchars($device['cpu']) ?></td>
                            <td><?= htmlspecialchars($device['ram']) ?></td>
                            <td><?= htmlspecialchars($device['storage']) ?></td>
                            <td>
                                <form method="post" class="request-form">
                                    <input type="hidden" name="device" value="<?= $device['assetno'] ?>">
                                    <input type="number" name="length" placeholder="Days" required min="1" max="365">
                                    <input type="text" name="reason" placeholder="Reason" required>
                                    <button type="submit" class="btn">Request</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No devices available.</p>
        <?php endif; ?>
    </div>

    <script>
        // Clear filter parameters on page load if they resulted in no devices
        if (!document.querySelector('.user-table')) {
            history.replaceState(null, '', window.location.pathname);
        }
    </script>
</body>
</html>
<?php
$connect->close();
?>