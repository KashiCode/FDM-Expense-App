<?php
    session_start();
    if(!isset($_SESSION['employeeId'])){
        header("Location: ../login.html");
    }
    if($_SESSION['role'] != 'Manager'){
        header("Location: ../login.html");
    }
    require_once "models/DatabaseManager.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FDM Expense App</title>
    <link rel="stylesheet" href="../css/style.css">

</head>
<body>
<div class="container">
        <!-- Navbar -->
        <nav class="navbar">
            <a href="#"><img class="logo" src="../images/FDM_Group_Logo_White.png" width="200" alt="FDM Logo"></a>
            <div class="nav-links">
                <form method="POST" action="../php/logout.php" style="display: inline;">
                    <button class="Btn" type="submit">
                        <div class="sign">
                            <svg viewBox="0 0 512 512"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg>
                        </div>
                        <div class="text">Logout</div>
                    </button>
                </form>
            </div>
        </nav>
        <br>


        <!-- User Alerts -->
        <section class="active-alerts">
            <h3>⚠️ Account Alert ⚠️</h3>
            <div class="report">
                <h4>New Claim Added </span></h4>
                <p>[Summary of claim]</p>
            </div>
        </section>

        <section class="weather-map">
            <h3>View All Claims</h3>
            <div class="tabs">
                <button>Search Claims</button>
                <button>Filter Incomplete</button>
                <button>Filter Complete</button>
            </div>
            <br>
            <?php
                $conn = DatabaseManager::getInstance()->getConnection();

                $sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
                        FROM expense_claims 
                        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
                        WHERE employees.manager = :managerId";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':managerId', $_SESSION['employeeId'], PDO::PARAM_INT);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $claims = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($claims as $claim) {
                        echo "<div class='report'>";
                        echo "<h4>📝 Claim " . $claim['claimId'] . "</h4>";
                        echo "<h5>Employee: " . $claim['firstName'] . " " . $claim['lastName'] . "</h5>"; // Show full name
                        echo "<h4>Amount: ". $claim['currency'] . " " . $claim['amount'] . "</h4>";
                        echo "<p>" . $claim['description'] . "</p>";

                        if ($claim['status'] == 'Pending') {
                            echo "<div class='badges'> <button class='blue'>Approval Required</button> </div>";
                            echo "<button>Accept Claim</button> <button>Reject Claim</button> <button onclick=\"window.location.href='view_claim.php?id=" . $claim['claimId'] . "'\">View Claim</button>";
                        } else if ($claim['status'] == 'Rejected') {
                            echo "<div class='badges'> <button class='red'>Rejected</button> </div>";
                            echo "<button onclick=\"window.location.href='view_claim.php?id=" . $claim['claimId'] . "'\">View Claim</button>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<p>No claims found.</p>";
                }
            ?>
        </section>
    </div>
</html>

