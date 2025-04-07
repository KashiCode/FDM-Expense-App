<?php
session_start();
if (!isset($_SESSION['employeeId'])) {
    header("Location: ../login.html");
    exit();
}
if ($_SESSION['role'] != 'Manager') {
    header("Location: ../login.html");
    exit();
}
require_once "models/DatabaseManager.php";

if (!isset($_GET['id'])) {
    echo "Claim ID is not provided.";
    exit();
}

$conn = DatabaseManager::getInstance()->getConnection();
$id = $_GET['id'];
$sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
        FROM expense_claims 
        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
        WHERE employees.manager = :managerId AND expense_claims.claimId = :claimId;"; // Use :claimId

$stmt = $conn->prepare($sql);
$stmt->bindParam(':managerId', $_SESSION['employeeId'], PDO::PARAM_INT);
$stmt->bindParam(':claimId', $id, PDO::PARAM_INT); // Bind $id
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $claim = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $claim = $claim[0]; // Access the first row
} else {
    $claim = null; // Set $claim to null if no results are found
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Claim</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">FDM Expense App</div>
            <div class="nav-links">
                <button class="Btn">
                    <div class="sign"><svg viewBox="0 0 512 512"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg></div>
                    <div class="text">Logout</div>
                </button>
            </div>
        </nav>
        <br>
        <section class="weather-map">
            <?php
            if ($claim) { // Check if $claim exists
                echo "<h1 style='text-align:center;'>📝 Claim " . $claim['claimId'] . "</h1>";
                echo "<div class='report' style='text-align: center;'>";
                echo "<h4>Employee: " . $claim['firstName'] . " " . $claim['lastName'] . "</h4>";
                echo "<h3>Amount: " . $claim['currency'] . " " . $claim['amount'] . "</h3>";
                echo "<p>" . $claim['description'] . "</p>";
                echo "<br>";
                echo "<h3>Evidence:</h3>";
                echo "<img src='" . $claim['evidenceFile'] . "' alt='Claim Evidence' style='width: 60%; border-radius: 10px; margin: 10px;'>";
                if ($claim['status'] == 'Pending') {
                    echo "<div class='badges'> <button class='blue'>Approval Required</button> </div>";
                    echo "<br>";

                    echo "<form method='POST' action='../php/process_claim.php' style='display: inline;'>";
                    echo "<input type='hidden' name='claimId' value='" . $claim['claimId'] . "'>";
                    echo "<input type='hidden' name='action' value='approve'>";
                    echo "<button type='submit' class='confirm-button' data-action='approve'>Accept Claim</button>";
                    echo "</form>";

                    echo "<form method='POST' action='../php/process_claim.php' style='display: inline;'>";
                    echo "<input type='hidden' name='claimId' value='" . $claim['claimId'] . "'>";
                    echo "<input type='hidden' name='action' value='reject'>";
                    echo "<button type='submit' class='confirm-button' data-action='reject'>Reject Claim</button>";
                    echo "</form>";
                } else if ($claim['status'] == 'Rejected') {
                    echo "<div class='badges'> <button class='red'>Rejected</button> </div>";
                }
                echo "</div>";
            } else {
                echo "<p>Claim not found or you do not have permission to view it.</p>";
            }
            ?>
        </section>
    </div>

    <script>
        document.querySelectorAll('.confirm-button').forEach(button => {
            button.addEventListener('click', (e) => {
            const action = button.getAttribute('data-action'); // This is either approve or reject
            if (!confirm(`Are you sure you want to ${action} this claim?`)) {
                e.preventDefault(); // Cancel 
            }
            });
        });
    </script>
</body>
</html>