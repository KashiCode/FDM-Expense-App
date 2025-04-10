<?php
session_start();
require_once "models/DatabaseManager.php";

// Check manager is logged in
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Manager') {
    header("Location: ../loginPage.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claimId']) && isset($_POST['action'])) { //Only go forward if POST, claimid and approve/reject is set
    $claimId = $_POST['claimId'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $managerId = $_SESSION['employeeId'];
    $managerMessage = $_POST['managerMessage'] ?? null; //If its not set, then set to null
    $username = $_SESSION['username'];

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


    if ($action == 'approve') {
        // If approved, update the spending limit of the manager
        $sql = "UPDATE managers SET spendingLimit = spendingLimit - (SELECT amount FROM expense_claims WHERE claimId = :claimId LIMIT 1) WHERE managerId = :managerId";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':managerId' => $managerId, ':claimId' => $claimId]);

        $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:managerId, (SELECT username FROM employees WHERE employeeId = :managerId), 'Manager', :event, NOW())";
        $event = $username . " Accepted Claim " . $claimId;
        $stmt = $conn->prepare($sql);
        $stmt->execute([':managerId' => $managerId, ':event' => $event]);
    } else {
        // If rejected, log the event
        $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:managerId, (SELECT username FROM employees WHERE employeeId = :managerId), 'Manager', :event, NOW())";
        $event = $username . " Rejected Claim " . $claimId;
        $stmt = $conn->prepare($sql);
        $stmt->execute([':managerId' => $managerId, ':event' => $event]);
    }
  
    // Redirect to notification page
    echo "<form name='notification' method='POST' action='../php/notification.php' enctype='multipart/form-data'>";
    echo "<input type='hidden' name='action' value='".$action."'>";
    echo "<input type='hidden' name='claimId' value='" .$claimId. "'>";
    echo "<input type='hidden' name='note' value='" .$managerMessage. "'>";
    echo "<input type='hidden' name='redir' value='../php/manager_dashboard.php'>";
    echo "</form>";
    echo "<script type='text/javascript'>document.notification.submit();</script>";

    // Redirect back to dashboard
    //header("Location: ../php/manager_dashboard.php");
    exit;
} else {
    die("Invalid request.");
}