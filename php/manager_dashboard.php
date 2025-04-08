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
                    <svg viewBox="0 0 512 512"><path d="M377.9 105.9L500.7 228.7c7.2 7.2 11.3 17.1 11.3 27.3s-4.1 20.1-11.3 27.3L377.9 406.1c-6.4 6.4-15 9.9-24 9.9c-18.7 0-33.9-15.2-33.9-33.9l0-62.1-128 0c-17.7 0-32-14.3-32-32l0-64c0-17.7 14.3-32 32-32l128 0 0-62.1c0-18.7 15.2-33.9 33.9-33.9c9 0 17.6 3.6 24 9.9zM160 96L96 96c-17.7 0-32 14.3-32 32l0 256c0 17.7 14.3 32 32 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32l-64 0c-53 0-96-43-96-96L0 128C0 75 43 32 96 32l64 0c17.7 0 32 14.3 32 32s-14.3 32-32 32z"></path></svg>
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
                                <input type='hidden' name='managerMessage' value=''>
                                <button type='submit' class='confirm-button' data-action='reject'>Reject Claim</button>
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
                e.preventDefault(); // Because this is done now, the return below cancels
                const action = button.getAttribute('data-action');
                const form = button.closest('form');

                if (!confirm(`Are you sure you want to ${action} this claim?`)) {
                    return; // Exit if user cancels
                }

                if (action === 'reject') {
                    const managerMessage = prompt("Please provide a reason for rejection, or ask for employee elaboration (both optional):");
                    
                    // Only add what was typed in only if user didn't cancel the prompt
                    if (managerMessage !== null) {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'managerMessage';
                        input.value = managerMessage;
                        form.appendChild(input);
                    }
                }
                
                form.submit(); // Only submit text after all checks
            });
        });
    </script>
</body>
</html>