<?php
session_start();
require_once "models/DatabaseManager.php";
//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

//Load Composer's autoloader
require '../vendor/autoload.php';

// Check manager is logged in
if (!isset($_SESSION['employeeId'])) {
    header("Location: ../login.html");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ExpenseClaim']) && isset($_POST['email'])) { //Only go forward if POST, claimid and approve/reject is set
    $claimId = $_POST['ExpenseClaim'];
    $email = $_POST['email']; // employee email
    $type = $_POST['type']; // notification type
    $name = $_POST['name']; // receiver name  
    $Sname = $_POST['Sname']; // sender name  
    $note = $_POST['note']; // email content
    $subject = "";
    $contents = "";
    $end = "<p style='color:#ffffff; line-height:1.6;'>Best Regards,</p><p style='color:#ffffff; line-height:1.6;'>$Sname</p>";
    $redir = $_POST['redir']; // redirect page
    $managerId = $_SESSION['employeeId'];
    
    // construct subject line and contents based on notification options
    if ($type === "approve") {
        $subject = "Claim " . $claimId . " Approved!";
        $contents = "Your expense claim with ID " . $claimId . " has been approved.</p><p style='color:#ffffff; line-height:1.6;'> Note: " . $note ."";
    } elseif ($type === "reject") {
        $subject = "Claim " . $claimId . " Rejected!";
        $contents = "Your expense claim with ID " . $claimId . " has been rejected.</p><p style='color:#ffffff; line-height:1.6;'> Note: " . $note . "</p><p style='color:#ffffff; line-height:1.6;'> Please contact your manager for further details.";
    } elseif ($type === "info") {
        $subject = "More Information Needed About Expense Claim " . $claimId . ".";
        $contents = "Additional information is required for your expense claim with ID " . $claimId . ".</p><p style='color:#ffffff; line-height:1.6;'> Note: " . $note . "</p><p style='color:#ffffff; line-height:1.6;'> Please provide the requested details.";
    } elseif ($type === "newExpense") {
        $subject = "New Claim " . $claimId . " from ".$Sname. ".";
        $contents =  $Sname . " has created a new expense claim: Claim " . $claimId . ".</p><p style='color:#ffffff; line-height:1.6;'>";
        $end = '';
    } else {
        $subject = "Your Expense Claim " . $claimId . " has been reimbursed!";
        $contents =  " The full amount of your Expense Claim with ID: " . $claimId . " has been deposited into your registered account.</p><p style='color:#ffffff; line-height:1.6;'>If any issues occur, please feel free to contact the Finance Team.";
    }

    $finEmail = "
        <!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
        <html xmlns='http://www.w3.org/1999/xhtml'>
        <head>
            <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
            <meta name='viewport' content='width=device-width, initial-scale=1.0' />
            <title>Email Template</title>
        </head>
        <body style='margin:0; padding:0; background-color:#f4f4f4; font-family: Arial, sans-serif;'>
            <!-- Main Table -->
            <table width='100%' border='0' cellspacing='0' cellpadding='0' bgcolor='#f4f4f4'>
            <tr>
                <td align='center' valign='top'>
                <!-- Email Container -->
                <table width='600' border='0' cellspacing='0' cellpadding='0' bgcolor='#ffffff' style='margin-top:20px;'>
                    <!-- Header -->
                    <tr>
                    <td style='padding:20px; background:#1D2837; text-align:center;'>
                        <img src='https://i.imgur.com/9Pys1Lu.png' alt='FDM' width='200' style='display:block; margin:0 auto;' />
                    </td>
                    </tr>
                    <!-- Content -->
                    <tr>
                    <td style='padding:30px 20px; background:#121826'>
                        <div style='padding:30px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.15); background: #1D2837;'>
                            <h1 style='color:#ffffff; margin-top:0;'>$subject</h1>
                            <h3 style='color:#ffffff;'>Dear $name,</h3>
                            <p style='color:#ffffff; line-height:1.6;'>$contents</p>
                            <br>
                            
                        </div>
                    </td>
                    </tr>
                    <!-- Footer -->
                    <tr>
                    <td style='padding:20px; text-align:center; font-weight:bold; background:#121826;'>
                        <a href='../index.html' style='display:inline-block; padding:10px 20px; background:#1F2937; color:#fff; text-decoration:none; border-radius:5px;'>Open Expenses App</a>
                    </td>
                    </tr>
                </table>
                </td>
            </tr>
            </table>
        </body>
        </html>";

    //use PHPmailer to create email
    //Create a PHPMailer instance; passing `true` enables exceptions
    $mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.mailersend.net';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'MS_GS6vwk@test-p7kx4xw1098g9yjr.mlsender.net';                     //SMTP username
        $mail->Password   = 'mssp.XFfv2jh.pq3enl60n57l2vwr.yhxoMZi';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 2525;                                    //TCP port to connect to;

        //Recipients
        $mail->setFrom('expenses@test-p7kx4xw1098g9yjr.mlsender.net', 'FDM Expenses');
        $mail->addAddress($email, $name);     //Add a recipient

        //Content
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $finEmail;

        $mail->send();
        echo 'Message has been sent';
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    // Display email content
    echo $finEmail;

    // Use JavaScript to redirect after a delay
    /*
    if (!empty($redir)) {
        echo "<script>
            setTimeout(function() {
                window.location.href = '" . htmlspecialchars($redir, ENT_QUOTES, 'UTF-8') . "';
            }, 5000); // Redirect after 5 seconds
        </script>";
    } else {
        echo "Redirection URL is missing.";
    }
    */
    header("Location: ".$redir);
} else {
    die("Invalid request.");
}

exit;
?>
