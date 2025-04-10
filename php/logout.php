<?php
session_start();
require_once 'models/DatabaseManager.php';
$employeeId = $_SESSION["employeeId"] ?? null;
$role = $_SESSION["role"] ?? null;
$username = $_SESSION['username'];

$conn = DatabaseManager::getInstance()->getConnection();
$sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:employeeId, :username, :role, :event, NOW())";
$event = $username . " Logged Out";
$stmt = $conn->prepare($sql);
$stmt->execute([':employeeId' => $employeeId, ':username' => $username, ':event' => $event, ':role' => $role]);
// Unset all session variables
session_unset();
session_destroy();

header("Location: ../index.html");
exit();
?>
