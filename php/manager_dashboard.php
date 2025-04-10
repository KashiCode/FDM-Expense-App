<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Manager') {
    header("Location: ../login.html");
    exit();
}
require_once "models/DatabaseManager.php";

$conn = DatabaseManager::getInstance()->getConnection();

// Build dynamic SQL query
$sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
        FROM expense_claims 
        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
        WHERE employees.manager = :managerId
        AND expense_claims.status IN ('Pending', 'Approved', 'Rejected')";
$params = [':managerId' => $_SESSION['employeeId']];

$filters = [];
if (!empty($_GET['date'])) {
    $filters[] = "DATE(date) = :date";
    $params[':date'] = $_GET['date'];
}
if (!empty($_GET['category'])) {
    $filters[] = "category LIKE :category";
    $params[':category'] = '%' . $_GET['category'] . '%';
}
if (!empty($_GET['amount'])) {
    $filters[] = "amount = :amount";
    $params[':amount'] = $_GET['amount'];
}
if (!empty($_GET['status'])) {
    $filters[] = "status = :status";
    $params[':status'] = $_GET['status'];
}

if (!empty($filters)) {
    $sql .= " AND " . implode(" AND ", $filters);
}

$sql .= " ORDER BY expense_claims.status ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
	@@ -16,80 +45,95 @@
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
                        <svg viewBox="0 0 512 512"><path d="..."></path></svg>
                    </div>
                    <div class="text">Logout</div>
                </button>
            </form>
        </div>
    </nav>
    <br>

    <?php
    $sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
        FROM expense_claims 
        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
        WHERE expense_claims.date < (SELECT loggedIn FROM employees WHERE employeeId = :employeeId) 
        AND expense_claims.status = 'Pending' 
        ORDER BY expense_claims.date DESC LIMIT 1";
    $params = [':employeeId' => $_SESSION['employeeId']];

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $alertclaims = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($alertclaims)) {
        echo '<section class="active-alerts">
                <h3>⚠️ Account Alert ⚠️</h3>';
        foreach ($alertclaims as $alert) {
            echo '<div class="report">
                    <h4>New Claim Added</h4>
                    <p>Employee: ' . htmlspecialchars($alert['firstName'] . ' ' . $alert['lastName']) . ', Amount: ' . htmlspecialchars($alert['currency'] . ' ' . $alert['amount']) . '</p>
                  </div>';
        }
        echo '</section>';
    }
    
    ?>


    <!-- Claim Filter + Results -->
    <section class="weather-map">
        <h3>View All Claims</h3>

        <!-- Filter Form -->
        <form method="GET">
            <label for="status">Status:</label>
            <select id="filter-select" name="status" id="status">
                <option value="">All</option>
                <option value="Pending" <?= ($_GET['status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= ($_GET['status'] ?? '') == 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Rejected" <?= ($_GET['status'] ?? '') == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>

            <label for="date">Date:</label>
            <input id="filter-date" type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

            <label for="category">Category:</label>
            <select id="filter-select" name="category">
                    <option value="">All Categories</option>
                    <option value="Travel" <?= ($_GET['category'] ?? '') === 'Travel' ? 'selected' : '' ?>>Travel</option>
                    <option value="Food" <?= ($_GET['category'] ?? '') === 'Food' ? 'selected' : '' ?>>Food</option>
                    <option value="Office Supplies" <?= ($_GET['category'] ?? '') === 'Office Supplies' ? 'selected' : '' ?>>Office Supplies</option>
                    <option value="Accommodation" <?= ($_GET['category'] ?? '') === 'Accommodation' ? 'selected' : '' ?>>Accommodation</option>
                    <option value="Fuel" <?= ($_GET['category'] ?? '') === 'Fuel' ? 'selected' : '' ?>>Fuel</option>
                </select>
            <label for="amount">Amount:</label>
            <input id="filter-text" type="number" step="0.01" name="amount" value="<?= $_GET['amount'] ?? '' ?>" placeholder="Enter Amount">

            <button type="submit">Apply Filters</button>
            <a href="manager_dashboard.php"><button type="button">Reset</button></a>
        </form>

        <br>

        <?php if ($claims): ?>
            <?php foreach ($claims as $claim): ?>
                <div class="report">
                    <h4>📝 Claim <?= $claim['claimId'] ?></h4>
                    <h5>Employee: <?= $claim['firstName'] . ' ' . $claim['lastName'] ?></h5>
                    <h4>Amount: <?= $claim['currency'] . ' ' . $claim['amount'] ?></h4>
                    <p><?= $claim['description'] ?></p>

                    <?php if ($claim['status'] == 'Pending') { ?>
                            <div class='badges'>
                                <button class='blue'>Approval Required</button>
                            </div>

                            <!-- Approve Form -->
                            <form method='POST' action='../php/process_claim.php' style='display: inline;'>
                                <input type='hidden' name='claimId' value='<?php echo $claim['claimId']; ?>'>
                                <input type='hidden' name='action' value='approve'>
                                <button type='submit' class='confirm-button' data-action='approve'>Approve Claim</button>
                            </form>

                            <!-- Reject Form -->
                            <form method='POST' action='../php/process_claim.php' style='display: inline;'>
                                <input type='hidden' name='claimId' value='<?php echo $claim['claimId']; ?>'>
                                <input type='hidden' name='action' value='reject'>
                                <button type='submit' class='confirm-button' data-action='reject'>Reject Claim</button>
                            </form>

                            <!-- Request More Information Form -->
                            <form method='POST' action='../php/notification.php' style='display: inline;'>
                                <input type='hidden' name='claimId' value='<?php echo $claim['claimId']; ?>'>
                                <input type='hidden' name='action' value='info'>
                                <input type='hidden' name='redir' value='../php/manager_dashboard.php'>
                                <button type='submit' class='confirm-button' data-action='info'>Request More Information</button>
                            </form>

                            <button onclick="window.location.href='view_claim.php?id=<?php echo $claim['claimId']; ?>'">View Claim</button>
                        <?php } else if ($claim['status'] == 'Rejected') { ?>
                            <div class='badges'>
                                <button class='red'>Rejected</button>
                            </div>
                            <button onclick="window.location.href='view_claim.php?id=<?php echo $claim['claimId']; ?>'">View Claim</button>
                        <?php } else if ($claim['status'] == 'Approved') { ?>
                            <div class='badges'>
                                <button class='green'>Approved</button>
                            </div>
                            <button onclick="window.location.href='view_claim.php?id=<?php echo $claim['claimId']; ?>'">View Claim</button>
                        <?php } ?>
                        </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No claims found.</p>
                        <?php endif; ?>
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