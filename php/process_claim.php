<?php
session_start();
require_once "models/DatabaseManager.php";

// Check manager is logged in
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Manager') {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claimId']) && isset($_POST['action'])) { //Only go forward if POST, claimid and approve/reject is set
    $claimId = $_POST['claimId'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $managerId = $_SESSION['employeeId'];
    $managerMessage = $_POST['managerMessage'] ?? null; //If its not set, then set to null

    $conn = DatabaseManager::getInstance()->getConnection();

    // Check the claim belongs to the manager's domain
    $sql = "SELECT * FROM expense_claims 
            INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
            WHERE claimId = :claimId AND employees.manager = :managerId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':claimId', $claimId, PDO::PARAM_INT);
    $stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        die("Claim out of your domain!");
    }

    // Update claim status
    $newStatus = ($action == 'approve') ? 'Approved' : 'Rejected'; //If approve, then true. true = approved, false = rejected
    $updateSql = "UPDATE expense_claims SET status = :status, managerMessage = :managerMessage WHERE claimId = :claimId";

    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bindParam(':status', $newStatus);
    $updateStmt->bindParam(':claimId', $claimId); //Binded for security
    $updateStmt->bindParam(':managerMessage', $managerMessage);
    $updateStmt->execute();

    // Redirect back to dashboard
    header("Location: ../php/manager_dashboard.php");
    exit;
} else {
    die("Invalid request.");
}