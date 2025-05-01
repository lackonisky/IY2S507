<?php
session_start();

if (isset($_SESSION['Active']) && $_SESSION['Active'] === 1) {
    if ($_SESSION['access'] == 1) {
        header("Location: admin.php");
    } else {
        header("Location: home.php");
    }
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
//
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
//checks if accout is active, password is correct, and sets session variables
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if ($user['active'] === 0) {
            $errorMessage = "Your account is not active. Please contact your administrator.";
        } else if ($user['active'] !== 1) {
            $errorMessage = "Your account has been disabled. Please contact your administrator.";
        } else if (password_verify($password, $user['hash'])) {
            $_SESSION['Active'] = 1;
            $_SESSION['UserID'] = $user['uid'];
            $_SESSION['FirstName'] = $user['firstname'];
            $_SESSION['LastName'] = $user['lastname'];
            $_SESSION['Email'] = $user['email'];
            $_SESSION['EmployeeNum'] = $user['employeenum'];
            $_SESSION['Department'] = $user['dept'];
            $_SESSION['access'] = $user['access'];
//redirect based on access level
            if ($user['access'] == 1) {
                header("Location: admin.php");
            } else {
                header("Location: home.php");
            }
            exit();
        } else {
            $errorMessage = "Invalid password.";
        }
    } else {
        $errorMessage = "User not found.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <?php if (isset($errorMessage)): ?>
        <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
    <?php endif; ?>
    <form method="POST">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" class="btn" value="Login">

        <input type="button" class="btn" value="Register" onclick="window.location.href='new_user.php';">
    </form>
</div>
</body>
</html>