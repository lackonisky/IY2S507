<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['Active']) || $_SESSION['Active'] !== 1) {
    header("Location: login.php");
    exit();
}

// Database connection
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

$connect = new mysqli('127.0.0.1', $_ENV['UPDATEUSER'], $_ENV['UPDATEPASS'], $_ENV['DATABASE']);
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

// Handle user updates
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action'])) {
    $userId = intval($_SESSION['UserID']);

    if ($_POST['action'] === 'update') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $employeenum = $_POST['employeenum'];
        $dept = $_POST['dept'];

        $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, employeenum = ?, dept = ? WHERE uid = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sssssi", $firstname, $lastname, $email, $employeenum, $dept, $userId);

        if ($stmt->execute()) {
            $message = "Account updated successfully.";
        } else {
            $error = "Failed to update account.";
        }
        $stmt->close();
    }

    if ($_POST['action'] === 'password' && !empty($_POST['new_password'])) {
        $newPassword = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

        $sql = "UPDATE users SET hash = ? WHERE uid = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("si", $newPassword, $userId);

        if ($stmt->execute()) {
            $message = "Password updated successfully.";
        } else {
            $error = "Failed to update password.";
        }
        $stmt->close();
    }
}
$dept = [];
$sql = "SELECT names FROM departments";
$result = $connect->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dept[] = $row['names'];
    }
}
// Fetch the logged-in user's data
$userId = intval($_SESSION['UserID']);
$sql = "SELECT firstname, lastname, email, employeenum, dept FROM users WHERE uid = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Management</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <nav class="navbar">
        <ul class="nav-items">
            <li><a href="home.php">Home</a></li>
            <li><a href="devices.php">Devices</a></li>
            <li><a href="my_devices.php">My Devices</a></li>
            <li class="logout"><a href="logout.php">Logout</a></li>
            <li class="account"><a href="account.php" class="active">Account</a></li>
        </ul>
    </nav>
    <div class="container">
        <h2>Account Management</h2>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            <label for="employeenum">Employee Number:</label>
            <input type="text" id="employeenum" name="employeenum" value="<?= htmlspecialchars($user['employeenum']) ?>" required>
            <label for="dept">Department:</label>
            <select id="dept" name="dept" required>
                <option value="" disabled selected><?= htmlspecialchars($user['dept']) ?></option>
                <?php foreach ($dept as $dept): ?>
                    <option value="<?= htmlspecialchars($dept) ?>"><?= htmlspecialchars($dept) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn">Update Account</button>
        </form>
        <form method="POST">
            <input type="hidden" name="action" value="password">
            <label for="new_password">New Password:</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password" required>
            <button type="submit" class="btn">Change Password</button>
        </form>
    </div>
</body>
</html>