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
        $contents = "Your expense claim with ID " . $claimId . " has been approved.</p><p> Note: " . $note;
    } elseif ($type === "reject") {
        $subject = "Claim " . $claimId . " Rejected!";
        $contents = "Your expense claim with ID " . $claimId . " has been rejected.</p><p> Note: " . $note . "</p><p> Please contact your manager for further details.";
    } else {
        $subject = "More Information Needed About Claim" . $claimId . ".";
        $contents = "Additional information is required for your expense claim with ID " . $claimId . ".</p><p> Note: " . $note . "</p><p> Please provide the requested details.";
    }

    $email = "<!DOCTYPE html>
    <html>
    <head>
    <style>
        /* Global Styles */
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

        body {
        background-color: #121826;
        color: #ffffff;
        font-family: 'Inter', sans-serif;
        margin: 0;
        padding: 0;
        background: radial-gradient(50% 50% at 50% 50%, #253244 0%, #141D2A 100%);
        min-height: 100vh;
        }

        .container {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 24px;
        padding: 24px;
        margin-top: 98px;
        max-width: 1440px;
        margin-left: auto;
        margin-right: auto;
        align-items: center;
        }

        /* Navbar */
        .navbar {
        width: 100%;
        height: 98px;
        background: #1D2837;
        display: flex;
        align-items: center;
        padding: 0 40px;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 10;
        box-shadow: 0px 4px 12px rgba(0, 0, 0, 0.1);
        }

        .navbar .logo {
        color: #FFF;
        font-size: 24px;
        font-weight: 700;
        }

        .home-button {
            display: flex;
            justify-content: center; 
            margin-bottom: 20px;
        }


        .home-button a {
            text-decoration: none;
            font-weight: bold;
            background-color: #1F2937;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }

        /* style for Main View Claims/Users UI */
        /* Element Names Require Changing */
        .weather-map, .active-alerts {
        grid-column: span 12;
        border-radius: 16px;
        border: 1px solid rgba(255, 255, 255, 0.15);
        background: #1D2837;
        padding: 32px;
        box-shadow: 0px 6px 20px rgba(0, 0, 0, 0.2);
        }

        .weather-map h3, .active-alerts h3 {
        margin: 0 0 24px 0;
        font-size: 24px;
        font-weight: 500;
        }
    </style>
    </head>
    <body>
        <!-- Navbar -->
        <nav class='navbar'>
            <a href='#'><img class='logo' src='../images/FDM_Group_Logo_White.png' width='200' alt='FDM Logo'></a>
        </nav>
        <br>
        <div class='container'>
            <!-- User Table Section -->
            <section class='weather-map'>
                <h1>$subject</h1>
                <h3>Dear $name,</h3>
                <br>
                <p>$contents</p>
                <br>
                <p>Best regards,</p>
                <p>Your Manager</p>
            </section>
            <div class='home-button'>
                <a href='../index.html'>Open Expenses App</a>
            </div>
        </div>
    </body>
    </html>";

    //use mail() function to send email
    mail($email,$subject,$contents); //- working dependant on localserver settings/smtp set up
    //echo $email;

    // Display email content
    echo $email;

    // Use JavaScript to redirect after a delay
    echo "<script>
        setTimeout(function() {
            window.location.href = '../php/manager_dashboard.php';
        }, 5000); // Redirect after 5 seconds
    </script>";
} else {
    die("Invalid request.");
}

exit;
?>
