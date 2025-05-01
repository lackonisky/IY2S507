<?php
session_start();
// Check if the user is logged in
if(isset($_SESSION["Active"]) && $_SESSION["Active"] === 1) {
    exit(header("location: home.php"));
}
// Load external libraries from composer
require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;
use ZxcvbnPhp\Zxcvbn;
// Gets variables from the .env file
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Create connection
$connect = new mysqli('127.0.0.1', $_ENV['INSERTUSER'], $_ENV['INSERTPASS'], $_ENV['DATABASE']);

// Test connection
if ($connect->connect_error) {
    die("Connection failed: " . $connect->connect_error);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password']) && !isset($_POST['firstname'])) {
    $password = $_POST['password'];
    $zxcvbn = new Zxcvbn();
    $strength = $zxcvbn->passwordStrength($password);
    echo json_encode(['score' => $strength['score'], 'feedback' => $strength['feedback']]);
    exit();
} // this if is ai generated to handle the password strength check and checks to see if it is a full form submission or a strength check.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $employeenum = $_POST['employeenum'];
    $dept = $_POST['dept'];
    // Takes the variables from the html form and assigns them to variables.

// Check if the email already exists
    $Sql = "SELECT email FROM users WHERE email = ?";
    $Stmt = $connect->prepare($Sql);
    $Stmt->bind_param("s", $email);
    $Stmt->execute();
    $Result = $Stmt->get_result();
// CHeck if the employee number already exists
    $enSql = "SELECT employeenum FROM users WHERE employeenum = ?";
    $enStmt = $connect->prepare($Sql);
    $enStmt->bind_param("s", $email);
    $enStmt->execute();
    $enResult = $enStmt->get_result();
//validation to ensure that inputs are correct
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
        //hash the password
        $hash = password_hash($password, PASSWORD_BCRYPT);

        //set default values
        $limit = 0;
        $active = 0;
        $access = 0;
        $sql = "INSERT INTO users (firstname, lastname, email, hash, employeenum, dept, `limit`, active, access)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("ssssisiii", $firstname, $lastname, $email, $hash, $employeenum, $dept, $limit, $active, $access);
//binds variables to protect against sql injection
        if ($stmt->execute()) {
            echo "New user account created successfully";
            header("location: login.php");
        } else {
            echo "System Offline, Contact your administrator";
        }
// redirects or echo an error message
        $stmt->close();
    }
}
//get dept names
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
    <link rel="stylesheet" href="styles.css">
    <script> // AI generated code to handle the password strength check
        document.addEventListener("DOMContentLoaded", function () {
            const passwordInput = document.getElementById("password");
            const strengthMeter = document.getElementById("strength-meter");
            const strengthText = document.getElementById("strength-text");
            const submitButton = document.querySelector("input[type='submit']");

            passwordInput.addEventListener("input", function () {
                const password = passwordInput.value;

                fetch("new_user.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: new URLSearchParams({ password })
                })
                    .then(response => response.json())
                    .then(data => {
                        strengthMeter.value = data.score;
                        const strengthLevels = ["Very Weak", "Weak", "Fair", "Good", "Strong"];
                        strengthText.textContent = `Strength: ${strengthLevels[data.score]}`;
                        submitButton.disabled = data.score < 4;
                    })
                    .catch(error => console.error("Error:", error));
            });
        });
    </script>

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
            <meter id="strength-meter" min="0" max="4" value="0"></meter>
            <p id="strength-text">Strength: Very Weak</p>

            <label for="employeenum">Employee Number:</label>
            <input type="text" id="employeenum" name="employeenum" required>

            <label for="dept">Department:</label>
            <select id="dept" name="dept" required>
                <option value="" disabled selected>Select a department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= htmlspecialchars($department) ?>"><?= htmlspecialchars($department) ?></option>
                <?php endforeach; ?>
            </select>

            <input type="submit" class="btn" value="Create Account">
            <a href="./login.php">
                <input type="button" class="btn" value="Log In" />
            </a>
        </form>
    </div>

</body>
</html>
