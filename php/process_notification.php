<?php
session_start();
require_once "models/DatabaseManager.php";

// Check manager is logged in
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Manager') {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ExpenseClaim']) && isset($_POST['email'])) { //Only go forward if POST, claimid and approve/reject is set
    $claimId = $_POST['ExpenseClaim'];
    $email = $_POST['email']; // employee email
    $type = $_POST['type']; // notification type
    $name = $_POST['name']; // employee name  
    $note = $_POST['note']; // email content
    $subject = "";
    $contents = "";
    $managerId = $_SESSION['employeeId'];

    // construct subject line and contents based on notification options
    if ($type === "approve") {
        $subject = "Claim " . $claimId . " Approved!";
        $contents = "Your expense claim with ID " . $claimId . " has been approved. Note: " . $note;
    } elseif ($type === "reject") {
        $subject = "Claim " . $claimId . " Rejected!";
        $contents = "Your expense claim with ID " . $claimId . " has been rejected. Note: " . $note . " Please contact your manager for further details.";
    } else {
        $subject = "More Information Needed About " . $claimId . ".";
        $contents = "Additional information is required for your expense claim with ID " . $claimId . ". Note: " . $note . " Please provide the requested details.";
    }

    //use mail() function to send email - placeholder code below to show constructed email
    //mail($email,$subject,$contents); - working dependant on localserver settings/smtp set up
    echo "<!DOCTYPE html>
    <html>
    <head>
    </head>
    <body>
        <h1>$subject</h1>
        <p>Dear $name,</p>
        <p>$contents</p>
        <p>Best regards,</p>
        <p>Your Manager</p>
    </body>
    </html>";
    sleep(5);
    //redirect back to manager dashboard - uncomment or will be stuck on page.
    //header("Location: ../php/manager_dashboard.php");
} else {
    die("Invalid request.");
}
?>
