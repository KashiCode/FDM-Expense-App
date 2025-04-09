<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../loginPage.php");
    exit();
}
require_once "models/DatabaseManager.php";

$employeeId = $_GET['employeeId'] ?? null;
if (!$employeeId) {
    echo "Employee ID is not provided.";
    exit();
}
$conn = DatabaseManager::getInstance()->getConnection();
$sql = "SELECT * FROM employees WHERE employeeId = :employeeId";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
$stmt->execute();
$employee = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$employee) {
    echo "Employee not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? null;
    $retypePassword = $_POST['retypePassword'] ?? null;

    if ($password && $retypePassword && $password === $retypePassword) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $sql = "UPDATE employees SET passwordHash = :password WHERE employeeId = :employeeId";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
        $stmt->bindParam(':employeeId', $employeeId, PDO::PARAM_INT);
        if ($stmt->execute()) {
            echo "Password updated successfully.";
            header("Location: admin_dashboard.php");
        } else {
            echo "Failed to update password.";
        }
    } else {
        echo "<p class='error'>Passwords do not match.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>FDM Expenses - Change Password</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <header>
        <nav>
            <a href="index.html"> <img class="logo" src="../images/FDM_Group_Logo_White.png"></a>
            <ul>

            </ul>
        </nav>
    </header>
    <h1>Change Password for <?php echo $employee['firstName'] . ' ' . $employee['lastName']; ?></h1>
    <form id="loginForm" method="POST" action="">
        <div id="error-message"></div>
        <input type="password" id="password" name="password" placeholder="Password" required>
        <br>
        <input type="password" id="password" name="retypePassword" placeholder="Retype Password" required>
        <br>
        <button type="submit">Change Password</button>
    </form>
</body>
</html>