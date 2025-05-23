<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] !== 'Employee') {
    header("Location: ../loginPage.php");
    exit();
}

require_once "models/DatabaseManager.php";

$employeeId = $_SESSION['employeeId'];
$conn = DatabaseManager::getInstance()->getConnection();

// Fetch the most recent approved or rejected claim
$sql = "SELECT * FROM expense_claims WHERE employeeId = :employeeId AND status IN ('Approved', 'Rejected','Reimbursed') ORDER BY date DESC LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->execute([':employeeId' => $employeeId]);
$recentClaim = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch all claims with optional filters
$sql = "SELECT * FROM expense_claims WHERE employeeId = :employeeId";
$params = [':employeeId' => $employeeId];

if (!empty($_GET['date'])) {
    $sql .= " AND DATE(date) = :date";
    $params[':date'] = $_GET['date'];
}
if (!empty($_GET['category'])) {
    $sql .= " AND category LIKE :category";
    $params[':category'] = '%' . $_GET['category'] . '%';
}
if (!empty($_GET['amount'])) {
    $sql .= " AND amount = :amount";
    $params[':amount'] = $_GET['amount'];
}

$status = $_GET['status'] ?? 'Pending';
if ($status !== 'All') {
    $sql .= " AND status = :status";
    $params[':status'] = $status;
}
$sql .= " ORDER BY date DESC";


$stmt = $conn->prepare($sql);
$stmt->execute($params);
$claims = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            <h1 style="margin-left:2rem">Employee Portal</h1>

            <div class="nav-links">
                <form method="POST" action="../php/logout.php">
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

        <section class="weather-map">
        <div class="tabs">
                <a href="create_expense_claim.php" class="create-claim-btn">Create New Claim</a>
            </div>
        </section>

        <!-- Alerts -->
        <section class="active-alerts">
            <h3>Latest Claim Alert</h3>
            <hr class="styled-hr">


            <?php if ($recentClaim): ?>
                <div class="report">
                    <p><strong>Claim Status:</strong> <?= $recentClaim['status'] ?></p>
                    <?php if (!empty($recentClaim['managerMessage'])): ?>
                            <p><strong>Manager's response:</strong>
                            <?= htmlspecialchars($recentClaim['managerMessage']) ?></p>
                    <?php endif; ?>
                    <p><strong>Amount:</strong> <?= $recentClaim['currency'] ?> <?= number_format($recentClaim['amount'], 2) ?></p>
                    <p><strong>Date Submitted:</strong> <?= date("d/m/Y H:i", strtotime($recentClaim['date'])) ?></p>
                    <p><strong>Category:</strong> <?= $recentClaim['category'] ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($recentClaim['description']) ?></p>
                    <?php if (!empty($recentClaim['evidenceFile']) && file_exists($recentClaim['evidenceFile'])): ?>
                        <p><strong>Evidence:</strong></p>
                        <a href="<?= $recentClaim['evidenceFile'] ?>" target="_blank">
                            <img src="<?= $recentClaim['evidenceFile'] ?>" alt="Receipt" style="max-width: 150px; border-radius: 6px; box-shadow: 0 0 5px rgba(0,0,0,0.2); margin-top: 10px;">
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p>No recent claims with approved or rejected status.</p>
            <?php endif; ?>
        </section>

        <!-- Claims Viewing Section -->
        <section class="weather-map">

            <h3>View Claims</h3>
            <hr class="styled-hr">

            <form method="GET" action="" style="display: flex; flex-wrap: wrap; gap: 20px;">
                <input id="filter-date" type="date" name="date" value="<?= $_GET['date'] ?? '' ?>">
                <select id="filter-select" name="category">
                    <option value="">All Categories</option>
                    <option value="Travel" <?= ($_GET['category'] ?? '') === 'Travel' ? 'selected' : '' ?>>Travel</option>
                    <option value="Food" <?= ($_GET['category'] ?? '') === 'Food' ? 'selected' : '' ?>>Food</option>
                    <option value="Office Supplies" <?= ($_GET['category'] ?? '') === 'Office Supplies' ? 'selected' : '' ?>>Office Supplies</option>
                    <option value="Accommodation" <?= ($_GET['category'] ?? '') === 'Accommodation' ? 'selected' : '' ?>>Accommodation</option>
                    <option value="Fuel" <?= ($_GET['category'] ?? '') === 'Fuel' ? 'selected' : '' ?>>Fuel</option>
                </select>
                <input id="filter-text" type="number" step="0.01" name="amount" value="<?= $_GET['amount'] ?? '' ?>" placeholder="Enter Amount">
                <select id="filter-select" name="status">
                    <option value="All" <?= ($status === 'All') ? 'selected' : '' ?>>All</option>
                    <option value="Pending" <?= ($status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Approved" <?= ($status === 'Approved') ? 'selected' : '' ?>>Approved</option>
                    <option value="Rejected" <?= ($status === 'Rejected') ? 'selected' : '' ?>>Rejected</option>
                    <option value="Reimbursed" <?= ($status === 'Reimbursed') ? 'selected' : '' ?>>Reimbursed</option>
                </select>
                <button type="submit">Filter</button>
            </form>
            <br>

            <?php if (empty($claims)): ?>
                <p style="text-align:center; font-style: italic;">No prior claims submitted.</p>
            <?php else: ?>
                <?php foreach ($claims as $index => $claim): ?>
                    <div class="report">
                        <h4>Claim <?= $index + 1 ?></h4>
                        <hr class="styled-hr">

                        <p><strong>Amount:</strong> <?= $claim['currency'] ?> <?= number_format($claim['amount'], 2) ?></p>
                        <p><strong>Date Submitted:</strong> <?= date("d/m/Y H:i", strtotime($claim['date'])) ?></p>
                        <p><strong>Category:</strong> <?= $claim['category'] ?></p>
                        <p><strong>Description:</strong> <?= htmlspecialchars($claim['description']) ?></p>
                        
                        <?php if (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])): ?>
                            <p><strong>Evidence:</strong></p>
                            <a href="<?= $claim['evidenceFile'] ?>" target="_blank">
                                <img src="<?= $claim['evidenceFile'] ?>" alt="Receipt" style="max-width: 150px; border-radius: 6px; box-shadow: 0 0 5px rgba(0,0,0,0.2); margin-top: 10px;">
                            </a>
                        <?php endif; ?>
                        <br>
                        <?php if ($claim['status'] === 'Pending'): ?>
                            <form method="GET" action="update_claim.php" style="display:inline;">
                                <input type="hidden" name="claimId" value="<?= $claim['claimId'] ?>">
                                <button type="submit">Edit Claim</button>
                            </form>
                            <form method="POST" action="delete_claim.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this claim?');">
                                <input type="hidden" name="claimId" value="<?= $claim['claimId'] ?>">
                                <button type="submit">Delete Claim</button>
                            </form>
                        <?php elseif ($claim['status'] === 'Rejected'): ?>
                            <?php if (!empty($recentClaim['managerMessage'])): ?>
                            <p><strong>Manager's response:</strong>
                            <?= htmlspecialchars($recentClaim['managerMessage']) ?></p>
                    <?php endif; ?>
                        <?php endif; ?>
                        <div class="badges">
                            <button class="<?= strtolower($claim['status']); ?>"><?= $claim['status'] ?></button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>