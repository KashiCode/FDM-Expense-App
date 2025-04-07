<?php
require_once "models/DatabaseManager.php";

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Missing claim ID.";
    exit;
}

$claimId = $_GET['id'];
$conn = DatabaseManager::getInstance()->getConnection();

try {
    $sql = "UPDATE expense_claims SET status = 'Approved' WHERE claimId = :claimId";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':claimId' => $claimId]);

    session_start();
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