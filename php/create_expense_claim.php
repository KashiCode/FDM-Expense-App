<?php
session_start();
require_once "models/ExpenseClaim.php";

// Ensure the user is logged in
if (!isset($_SESSION["employeeId"])) {
    header("Location: ../loginPage.php");
    exit();
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employeeId = $_SESSION["employeeId"];
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $currency = $_POST["currency"];

    // Handle file upload
    $evidenceFile = "";
    
    if (!empty($_FILES["evidenceFile"]["name"])) {
        $uploadDir = "uploads/";
        $fileType = strtolower(pathinfo($_FILES["evidenceFile"]["name"], PATHINFO_EXTENSION));
        $fileName = uniqid() . "_" . basename($_FILES["evidenceFile"]["name"]);
        $evidenceFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES["evidenceFile"]["tmp_name"], $evidenceFile)) {
            // File successfully uploaded
        } else {
            $message = "Error uploading file.";
        }
    }

    // Create the expense claim
    $claim = new ExpenseClaim();
    if ($claim->createClaim($employeeId, $amount, $description, $category, $evidenceFile, "", $currency)) {
        $conn = DatabaseManager::getInstance()->getConnection();
        $sql = "SELECT ec.*, CONCAT(e.firstName, ' ', e.lastName) AS employee_name, CONCAT(m.firstName, ' ', m.lastName) AS manager_name, m.email AS manager_email FROM expense_claims ec JOIN employees e ON ec.employeeId = e.employeeId JOIN employees m ON e.manager = m.employeeId WHERE ec.employeeId = :employeeId ORDER BY ec.date DESC LIMIT 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':employeeId' => $employeeId]);
        $newClaim = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<form name='notification' method='POST' action='../php/process_notification.php' enctype='multipart/form-data'>";
        echo "<input type='hidden' name='type' value='newExpense'>";
        echo "<input type='hidden' name='ExpenseClaim' value='" . $newClaim['claimId'] . "'>";
        echo "<input type='hidden' name='email' value='" . $newClaim['manager_email'] . "'>";
        echo "<input type='hidden' name='name' value='" . $newClaim['manager_name'] . "'>";
        echo "<input type='hidden' name='Sname' value='" . $newClaim['employee_name'] . "'>";
        echo "<input type='hidden' name='note' value=''>";
        echo "<input type='hidden' name='redir' value='./employee_dashboard.php'>";
        echo "</form>";
        echo "<script type='text/javascript'>document.notification.submit();</script>";
        // header("Location: employee_dashboard.php"); // Redirect to homepage
        exit();
    } else {
        $message = "Error submitting claim.";
    }

}

// Show message after redirection
if (isset($_SESSION["message"])) {
    $message = $_SESSION["message"];
    unset($_SESSION["message"]); // Remove message after displaying it
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Expense Claim</title>
    <link rel="stylesheet" href="../css/index.css">
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>
    <h1>Create Expense Claim</h1>

    <form method="post" action="" enctype="multipart/form-data">
        <label for="amount">Amount:</label>
        <div class="currecy-amount">
            <select id="currency" name="currency" required>
                <option value="GBP">GBP</option>
            </select>
            <input type="number" step="0.01" name="amount" min="0.01" required>
        </div>
        <label for="description">Description:</label>
        <textarea name="description" required></textarea>

        <label for="category">Category:</label>
        <select name="category" required>
            <option value="Travel">Travel</option>
            <option value="Food">Food</option>
            <option value="Office Supplies">Office Supplies</option>
            <option value="Accommodation">Accommodation</option>
            <option value="Fuel">Fuel</option>
        </select>

        <label for="evidenceFile">Upload Evidence:</label>
        <label class="file-upload" for="image">
            <input type="file" id="image" name="evidenceFile" accept="image/*" onchange="previewImage(event)" required>
            Choose Image
        </label>
        <img id="imagePreview" src="" style="display:none; max-width: 300px; margin-top: 10px;">

        <button type="submit">Submit Claim</button>
    </form>

    <p>
    </p>
    <div class="home-button">
        <a href="employee_dashboard.php">Homepage</a>
    </div>

</body>
</html>
