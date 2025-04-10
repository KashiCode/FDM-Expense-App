<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../loginPage.php");
    exit();
}
require_once "models/DatabaseManager.php";
$conn = DatabaseManager::getInstance()->getConnection();
$employeeId = $_GET['employeeId'] ?? null;
if (!$employeeId) {
    echo "Employee ID is not provided.";
    exit();
}

$sql = "SELECT managers.spendingLimit, employees.firstName, employees.lastName FROM managers INNER JOIN employees ON managers.managerId = employees.employeeId WHERE employees.employeeId = :employeeId";
$stmt = $conn->prepare($sql);
$stmt->execute([':employeeId' => $employeeId]);
$spendingLimit = $stmt->fetch(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newLimit = $_POST['spendingLimit'] ?? null;
    if ($newLimit !== null) {
        $sql = "UPDATE managers SET spendingLimit = ? WHERE managerId = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$newLimit, $employeeId]);
        header("Location: admin_dashboard.php?success=Spending limit updated successfully.");
        exit();
    } else {
        header("Location: admin_dashboard.php?error=Please enter a valid spending limit.");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico">
    <title>FDM Expenses - Update Spending Limit</title>
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
    <h1>Update Spending Limit for  <?php echo $spendingLimit['firstName'] . ' ' . $spendingLimit['lastName']; ?></h1>
    <form id="loginForm" method="POST" action="">
        <div id="error-message"></div>
        <input type="number" id="text" step="0.01" name="spendingLimit" placeholder="Spending Limit" min="0.01" value="<?= $spendingLimit['spendingLimit'] ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>
