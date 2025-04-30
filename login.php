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
$connect = new mysqli('127.0.0.1', $_ENV['SELECTUSER'], $_ENV['SELECTPASS'], $_ENV['DATABASE']);

// Test connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['hash'])) {
            // Store user information in session variables
            $_SESSION['Active'] = 1;
            $_SESSION['UserID'] = $user['id'];
            $_SESSION['FirstName'] = $user['firstname'];
            $_SESSION['LastName'] = $user['lastname'];
            $_SESSION['Email'] = $user['email'];
            $_SESSION['EmployeeNum'] = $user['employeenum'];
            $_SESSION['Department'] = $user['dept'];
            $_SESSION['access'] = $user['access'];
            // Redirect to the home page
            header("location: home.php");
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with this email.";
    }
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
