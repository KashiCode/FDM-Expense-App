<?php
session_start();
require_once "models/DatabaseManager.php";

// Ensure the user is logged in
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] !== 'Employee') {
    header("Location: ../loginPage.php");
    exit();
}


// Check if the claimId is passed
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claimId'])) {
    $claimId = $_POST['claimId'];
    $employeeId = $_SESSION['employeeId'];

    // Connect to the database
    $conn = DatabaseManager::getInstance()->getConnection();

    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
    $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:employeeId, (SELECT username FROM employees WHERE employeeId = :employeeId), :role, :event, NOW())";
    $event = $username . " Deleted Claim " . $claimId;
    $stmt = $conn->prepare($sql);
    $stmt->execute([':employeeId' => $employeeId, ':event' => $event, ':role' => $role]);

    // SQL query to delete the claim
    $sql = "DELETE FROM expense_claims WHERE claimId = ? AND employeeId = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$claimId, $employeeId]);

    // Redirect with a success or error message
    if ($result) {
        $_SESSION["message"] = "Claim deleted successfully!";
    } else {
        $_SESSION["message"] = "Error deleting claim. Please try again.";
    }
    
    // Redirect back to the claims page
    header("Location: employee_dashboard.php");
    exit();
} else {
    // Redirect if no claimId is provided
    $_SESSION["message"] = "Invalid claim ID.";
    header("Location: employee_dashboard.php");
    exit();
}
?>
