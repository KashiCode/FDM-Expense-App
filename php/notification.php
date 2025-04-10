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
    // Fetch the first and last name of the manager
    $sql = "SELECT firstName, lastName FROM employees WHERE employeeId = :managerId";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':managerId', $managerId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $manager = $stmt->fetch(PDO::FETCH_ASSOC);
        $managerFirstName = $manager['firstName'];
        $managerLastName = $manager['lastName'];
    } else {
        die("Manager not found!");
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

if (isset($action)) {
    foreach ($options as $key => $value) {
        if ($value === $action) {
            $selected = $value;
            break;
        }
    }
} else {
    $selected = $options[1]; // Default to 'approve' if no action is set
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
                <?php foreach ($options as $key => $value) { ?>
                    <option value="<?= $value ?>" <?= $value === $selected ? 'selected' : '' ?>><?= $typeText[$value] ?></option>
                <?php } ?>
            </select>
        </div>

        <!--Drop down menu for Expense Claim-->
        <label for="claim">Expense Claim:</label>
        <select name="ExpenseClaim" required>
            <!--Info from Expense Claim-->
            <?php
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
                        if ($claim['claimId'] == $claimId) {
                            echo "<option id=\"" . $claim['employeeId'] . "/" . $claim['firstName'] . "/" . $claim['email'] . "\" name='claimId' selected value=\"" . $claim['claimId'] . "\">📝 Claim " . $claim['claimId'] . "</option>";
                        } else {
                            echo "<option id=\"" . $claim['employeeId'] . "/" . $claim['firstName'] . "/" . $claim['email'] . "\" name='claimId' value=\"" . $claim['claimId'] . "\">📝 Claim " . $claim['claimId'] . "</option>";
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
        <textarea name="note"></textarea>
        <input type='hidden' name='redir' value='./manager_dashboard.php'>;
        <input type='hidden' name='Sname' value='<?= $managerFirstName ?> <?= $managerLastName ?>'>

        <button type="submit">Submit Notification</button>
    </form>

    <p>
    </p>
    <div class="home-button">
        <a href="manager_dashboard.php">Homepage</a>
    </div>

</body>
</html>
