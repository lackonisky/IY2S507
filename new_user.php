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
$connect = new mysqli('127.0.0.1', $_ENV['INSERTUSER'], $_ENV['INSERTPASS'], $_ENV['DATABASE']);

// Test connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $employeenum = $_POST['employeenum'];
    $dept = $_POST['dept'];

// Check if the email already exists
    $Sql = "SELECT email FROM users WHERE email = ?";
    $Stmt = $connect->prepare($Sql);
    $Stmt->bind_param("s", $email);
    $Stmt->execute();
    $Result = $Stmt->get_result();

    $enSql = "SELECT employeenum FROM users WHERE employeenum = ?";
    $enStmt = $connect->prepare($Sql);
    $enStmt->bind_param("s", $email);
    $enStmt->execute();
    $enResult = $enStmt->get_result();

    if ($Result->num_rows > 0) {
        $errorMessage = "Email already in use. Please use a different email.";
    } else if ($enResult->num_rows > 0) {
        $errorMessage = "Employee number already in use, if you believe this to be an error please contact your administrator.";
    } else if (empty($firstname) || empty($lastname) || empty($email) || empty($password) || empty($employeenum) || empty($dept)) {
        $errorMessage = "All fields are required.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Invalid email format.";
    } else if (strlen($password) < 8) {
        $errorMessage = "Password must be at least 8 characters long.";
    } else {
        // Proceed with user creation

        // Hash the password securely
        $hash = password_hash($password, PASSWORD_BCRYPT);

        // Set default values for limit and active
        $limit = 0;
        $active = 0;
        $access = 0;
        $sql = "INSERT INTO users (firstname, lastname, email, hash, employeenum, dept, `limit`, active, access)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssisiii", $firstname, $lastname, $email, $hash, $employeenum, $dept, $limit, $active, $access);

        if ($stmt->execute()) {
            echo "New user account created successfully";
            header("location: login.php");
        } else {
            echo "Error: " . $sql . "<br>" . $connect->error;
        }

        $stmt->close();
    }
}
// Fetch department names
$departments = [];
$sql = "SELECT names FROM departments";
$result = $connect->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row['names'];
    }
}
$connect->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Account Creation</title>
    <link rel="stylesheet" href="user_entry.css">
</head>
<body>
    <div class="container">
        <?php if (isset($errorMessage)): ?>
            <div class="error-message"><?= htmlspecialchars($errorMessage) ?></div>
        <?php endif; ?>
        <h2>Create User Account</h2>
        <form method="post">
            <label for="firstname">First Name:</label>
            <input type="text" id="firstname" name="firstname" required>

            <label for="lastname">Last Name:</label>
            <input type="text" id="lastname" name="lastname" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="employeenum">Employee Number:</label>
            <input type="text" id="employeenum" name="employeenum" required>

            <label for="dept">Department:</label>
            <select id="dept" name="dept" required>
                <option value="" disabled selected>Select a department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars($department) ?>"><?= htmlspecialchars($department) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="submit" value="Create Account">
            <a href="./login.php">
                <input type="button" value="Log In" />
            </a>
        </form>
    </div>

</body>
</html>
.