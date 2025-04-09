<?php
session_start();
require_once "models/ExpenseClaim.php";


// Ensure the user is logged in
if (!isset($_SESSION["employeeId"])) {
    header("Location: ../login.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['claimId']) && isset($_POST['action'])) { //Only go forward if POST, claimid and approve/reject is set
    $claimId = $_POST['claimId'];
    $action = $_POST['action']; // 'approve' or 'reject'
    $managerId = $_SESSION['employeeId'];

    $conn = DatabaseManager::getInstance()->getConnection();

    echo "hello";
    echo $action;
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
}

$options = [
    1 => 'approve',
    2 => 'reject',
    3 => 'info',
];

$typeText = [
    "approve" => 'Claim Approved',
    "reject" => 'Claim Rejected',
    "info" => 'More Information Needed',
];

$selected = null; // Initialize $selected with a default value
//set $selected to the appropriate action value in $typeText
if (isset($action)){
    for ($x = 1; $x <= 3; $x++) {
        if ($options[$x] === $action){
            $selected = $x;
        }
    }
}

$message = ""
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <h1>Notification</h1>

    <?php if ($message): ?>
        <p id="signUpMessage"><?php echo $message; ?></p>
    <?php endif; ?>

    <form method='POST' action="../php/process_notification.php" enctype="multipart/form-data">
        <!--Drop down menu for notification type-->
        <label for="type">Type:</label>
        <div class="notification-type-options">
            <select id="notification-type" name="type" required>
                <option value="" selected disabled hidden>Choose a Reason</option>
                <?php foreach ($options as $key) { ?>
                    <option value="<?= $key ?>" <?= $key === $selected ? 'selected' : '' ?>><?= $typeText[$key] ?></option>
                <?php } ?>
            </select>
        </div>

        <!--Drop down menu for Expense Claim-->
        <label for="claim">Expense Claim:</label>
        <select name="ExpenseClaim" required>
            <!--Info from Expense Claim-->
            <?php
                //check if form has been submitted (came from dashboard)
                if (!isset($action)){
                    echo "<option value=\"\" selected disabled hidden>Choose an Expense Claim</option>";
                }
                $conn = DatabaseManager::getInstance()->getConnection();

                //Fetch all pending expense claim IDs
                $sql = "SELECT * FROM `expense_claims`
                        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
                        WHERE STATUS = 'Pending';";
                $stmt = $conn->prepare($sql);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($claims as $claim) {
                        if ($claim['claimId'] === $claimId) {
                            echo "<option id=" .$claim['employeeId']."/".$claim['firstName']."/".$claim['email']. " name='claimId' selected value=". $claim['claimId'] .">📝 Claim " . $claim['claimId'] . "</option>";
                            echo "<option id=\"" .$claim['employeeId']."/".$claim['firstName']."/".$claim['email']. "\" name='claimId' selected value=\"". $claim['claimId'] ."\">📝 Claim " . $claim['claimId'] . "</option>";
                        } else {
                            echo "<option id=\"" .$claim['employeeId']."/".$claim['firstName']."/".$claim['email']. "\" name='claimId' value=\"". $claim['claimId'] ."\">📝 Claim " . $claim['claimId'] . "</option>";
                        }
                    }
                } else {
                    echo "<option disabled = 'disabled'>No claims found.</option>";
                }
            ?>
        </select>

        <!--Employee email and ID for selected expense claim-->
        <div id=detailsFlexbox style='display:grid; grid-template-columns: repeat(2, 1fr); grid-template-rows: repeat(3, 1fr)'>
            <label for="employeeID">Employee ID:</label>
            <p id=employeeIdText>n/a</p>
            <label for="employeeName">Employee Name:</label>
            <input type='hidden' name='name' id='name' value=''>
            <p id=employeeNameText>n/a</p>
            <label for="employeeEmail">Employee Email:</label>
            <input type='hidden' name='email' id='email' value=''>
            <p id=employeeEmailText>n/a</p>
            <script>
                var s = document.getElementsByName('ExpenseClaim')[0];
                s.addEventListener('change', function () {
                    var selectedOption = s.options[s.selectedIndex];
                    const textArray = selectedOption.id.split("/");
                    document.getElementById("employeeIdText").innerHTML = textArray[0] || "undefined";
                    document.getElementById("name").value = textArray[1] || "undefined";
                    document.getElementById("employeeNameText").innerHTML = textArray[1] || "undefined";
                    document.getElementById("email").value = textArray[2] || "undefined";
                    document.getElementById("employeeEmailText").innerHTML = textArray[2] || "undefined";
                });

                // Trigger the event listener on page load to set initial values
                s.dispatchEvent(new Event('change'));
            </script>
        </div>

        <!--Text Area for note-->
        <label for="note">Note:</label>
        <textarea name="note" required></textarea>

        <button type="submit">Submit Claim</button>
    </form>

    <p>
    </p>
    <div class="home-button">
        <a href="employee_dashboard.php">Homepage</a>
    </div>

</body>
</html>
