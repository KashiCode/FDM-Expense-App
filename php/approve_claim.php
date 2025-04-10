<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Manager') {
    header("Location: ../loginPage.php");
    exit();
}
require_once "models/DatabaseManager.php";

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Missing claim ID.";
    exit;
}

$claimId = $_GET['id'];
$managerId = $_SESSION['employeeId'] ?? null;
if (!$managerId) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}
$conn = DatabaseManager::getInstance()->getConnection();

try {
    $sql = "UPDATE expense_claims SET status = 'Approved' WHERE claimId = :claimId";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':claimId' => $claimId]);

    $role = $_SESSION['role'] ?? '';
    if ($role === 'Finance') {
        header("Location: finance_dashboard.php");
    } else {
        header("Location: manager_dashboard.php");
    }
    exit();
} catch (Exception $e) {
    echo "Error approving claim: " . $e->getMessage();
}