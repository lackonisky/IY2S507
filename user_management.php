<?php
session_start();

// Check if the user is an admin
if (!isset($_SESSION['access']) || $_SESSION['access'] != 1) {
    header("Location: home.php");
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
    $userId = intval($_POST['user_id']);

    if ($_POST['action'] === 'update') {
        $firstname = $_POST['firstname'];
        $lastname = $_POST['lastname'];
        $email = $_POST['email'];
        $employeenum = $_POST['employeenum'];
        $dept = $_POST['dept'];
        $access = intval($_POST['access']);
        $active = isset($_POST['active']) ? 1 : 0;

        $sql = "UPDATE users SET firstname = ?, lastname = ?, email = ?, employeenum = ?, dept = ?, access = ?, active = ? WHERE uid = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("sssssiis", $firstname, $lastname, $email, $employeenum, $dept, $access, $active, $userId);

        if ($stmt->execute()) {
            $message = "User updated successfully.";
        } else {
            $error = "Failed to update user.";
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
$departments = [];
$sql = "SELECT names FROM departments";
$result = $connect->query($sql);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['names'];
    }
}
// Fetch all users
$sql = "SELECT uid, firstname, lastname, email, employeenum, dept, access, active FROM users";
$result = $connect->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
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
    <div class="container">
        <h2>User Management</h2>
        <?php if (isset($message)): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <div class="error-message"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <table class="user-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Employee #</th>
                    <th>Department</th>
                    <th>Access Level</th>
                    <th>Status</th>
                    <th>Update</th>
                    <th>Password</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <form method="POST">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="user_id" value="<?= $user['uid'] ?>">
                            <td>
                                <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
                                <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
                            </td>
                            <td><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required></td>
                            <td><input type="text" name="employeenum" value="<?= htmlspecialchars($user['employeenum']) ?>" required></td>
                            <td>
                                <h3><?= htmlspecialchars($user['dept']) ?></h3>
                                <select id="dept" name="dept" required>
                                    <option value="" disabled selected>Select a department</option>
                                    <?php foreach ($departments as $department): ?>
                                        <option value="<?= htmlspecialchars($department) ?>"><?= htmlspecialchars($department) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="access">
                                    <option value="0" <?= $user['access'] == 0 ? 'selected' : '' ?>>User</option>
                                    <option value="1" <?= $user['access'] == 1 ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </td>
                            <td>
                                <input type="checkbox" name="active" <?= $user['active'] == 1 ? 'checked' : '' ?>>
                                Active
                            </td>
                            <td>
                                <button type="submit" class="btn">Update</button>
                            </td>
                        </form>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="action" value="password">
                                <input type="hidden" name="user_id" value="<?= $user['uid'] ?>">
                                <input type="password" name="new_password" placeholder="New Password" required>
                                <button type="submit" class="btn">Change Password</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>