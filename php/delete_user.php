<?php
require_once "models/DatabaseManager.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['employeeId'])) {
    $employeeId = $_POST['employeeId'];

    $conn = DatabaseManager::getInstance()->getConnection();

 
    $stmt = $conn->prepare("DELETE FROM managers WHERE managerId = ?");
    $stmt->execute([$employeeId]);

    
    $stmt = $conn->prepare("DELETE FROM employees WHERE employeeId = ?");
    $stmt->execute([$employeeId]);

    header("Location: admin_dashboard.php");
    exit();
} else {
    echo "Invalid request.";
}
