<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] !== 'Finance') {
    header("Location: ../login.html");
    exit();
}

require_once "models/DatabaseManager.php";
$conn = DatabaseManager::getInstance()->getConnection();

// Get current logged-in employee ID
$currentEmployeeId = $_SESSION['employeeId'];

$sql = "SELECT ec.*, e.firstName, e.lastName 
        FROM expense_claims ec
        INNER JOIN employees e ON ec.employeeId = e.employeeId
        WHERE ec.status IN ('Approved', 'Rejected')
          AND e.manager = (
              SELECT manager
              FROM employees
              WHERE employeeId = :currentEmployeeId
          )";

$params = [':currentEmployeeId' => $currentEmployeeId];

if (!empty($_GET['date'])) {
    $sql .= " AND DATE(ec.date) = :date";
    $params[':date'] = $_GET['date'];
}
if (!empty($_GET['category'])) {
    $sql .= " AND ec.category LIKE :category";
    $params[':category'] = '%' . $_GET['category'] . '%';
}
if (!empty($_GET['amount'])) {
    $sql .= " AND ec.amount = :amount";
    $params[':amount'] = $_GET['amount'];
}
if (!empty($_GET['status'])) {
    $sql .= " AND ec.status = :status";
    $params[':status'] = $_GET['status'];
} else {
    $sql .= " AND ec.status != 'Reimbursed'";
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
  <title>Finance Dashboard</title>
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

  <section class="active-alerts">
    <h3>📋 All Claims (Finance View)</h3>
    <form method="GET">
      <label>Status:</label>
      <select id="filter-select" name="status">
        <option value="">All</option>
        <option value="Approved" <?= ($_GET['status'] ?? '') == 'Approved' ? 'selected' : '' ?>>Approved</option>
        <option value="Rejected" <?= ($_GET['status'] ?? '') == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
      </select>

      <label>Date:</label>
      <input id="filter-text" type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

      <label>Category:</label>
      <select id="filter-select" name="category">
        <option value="">All</option>
        <option value="Food">Food</option>
        <option value="Travel">Travel</option>
        <option value="Office Supplies">Office Supplies</option>
        <option value="Accommodation">Accommodation</option>
        <option value="Fuel">Fuel</option>
      </select>

      <label>Amount:</label>
      <input id="filter-text" type="number" step="0.01" name="amount" value="<?= $_GET['amount'] ?? '' ?>">

      <button type="submit">Apply Filters</button>
      <a href="finance_dashboard.php"><button type="button">Reset</button></a>
    </form>
  </section>

  <section class="weather-map">
    <?php if (empty($claims)): ?>
      <p>No matching claims.</p>
    <?php else: ?>
      <?php foreach ($claims as $claim): ?>
        <div class="report" id="claim-<?= $claim['claimId'] ?>">
          <h4>Claim #<?= $claim['claimId'] ?></h4>
          <p><strong>Employee:</strong> <?= $claim['firstName'] . ' ' . $claim['lastName'] ?></p>
          <p><strong>Amount:</strong> <?= $claim['currency'] . ' ' . number_format($claim['amount'], 2) ?></p>
          <p><strong>Date:</strong> <?= date("d/m/Y", strtotime($claim['date'])) ?></p>
          <p><strong>Description:</strong> <?= htmlspecialchars($claim['description']) ?></p>

          <?php if (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])): ?>
            <p><strong>Evidence:</strong></p>
            <a href="<?= $claim['evidenceFile'] ?>" target="_blank">
              <img src="<?= $claim['evidenceFile'] ?>" alt="Evidence" style="max-width: 150px;">
            </a>
          <?php endif; ?>

          <br>
          <?php if ($claim['status'] == 'Pending'): ?>
            <div class="badges"><button class="blue">Pending Approval</button></div>
            <button onclick="approveClaim(<?= $claim['claimId'] ?>)">Approve</button>
            <button onclick="rejectClaim(<?= $claim['claimId'] ?>)">Reject</button>
          <?php elseif ($claim['status'] == 'Approved'): ?>
            <div class="badges"><button class="green">Approved</button></div>
            <button onclick="markAsReimbursed(<?= $claim['claimId'] ?>, <?= $claim['amount'] ?>)">💸 Mark as Reimbursed</button>
          <?php elseif ($claim['status'] == 'Rejected'): ?>
            <div class="badges"><button class="red">Rejected</button></div>
          <?php elseif ($claim['status'] == 'Reimbursed'): ?>
            <div class="badges"><button class="purple">Reimbursed</button></div>
          <?php endif; ?>

          <button onclick="window.location.href='view_claim.php?id=<?= $claim['claimId'] ?>'">🔍 View Details</button>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>

  <section style="margin-top: 40px;">
    <h3>📄 Generate Report</h3>
    <form method="POST" action="generate_report.php" target="_blank">
      <button type="submit">Download Reimbursed Claims (PDF)</button>
    </form>
  </section>
</div>

<script>
function markAsReimbursed(claimId, amount) {
  fetch('../php/finance_actions.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({ claimId, amount })
  })
  .then(res => res.json())
  .then(data => {
    alert(data.message);
    location.reload();
  });
}

function approveClaim(claimId) {
  if (confirm("Approve claim #" + claimId + "?")) {
    fetch('../php/approve_claim.php?id=' + claimId)
      .then(() => location.reload());
  }
}

function rejectClaim(claimId) {
  if (confirm("Reject claim #" + claimId + "?")) {
    fetch('../php/reject_claim.php?id=' + claimId)
      .then(() => location.reload());
  }
}
</script>
</body>
</html>
