<?php
session_start();
if(isset($_SESSION["Active"]) && $_SESSION["Active"] === 1) {
    exit(header("location: home.php"));
}
// Check if the user is logged in
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
// Gets environment variables from .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create connection
$connect = new mysqli($_ENV['SERVERNAME'], $_ENV['SELECTUSERNAME'], $_ENV['SELECTPASS'], $_ENV['DATABASENAME']);

// Test connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password securely
    $hash = password_hash($password, PASSWORD_BCRYPT);

    $sql = "SELECT uid FROM users WHERE email = ?";


    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    print_r(mysqli_num_rows($result));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
<div class="container">
    <h2>Login</h2>
    <form method="post">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <input type="submit" value="Login">
        <a href="./new_user.php">
            <input type="button" value="Register Here" />
        </a>
    </form>
</div>
</body>
</html>
