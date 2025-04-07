<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] !== 'Finance') {
    header("Location: ../login.html");
    exit();
}

require_once "models/DatabaseManager.php";
$conn = DatabaseManager::getInstance()->getConnection();

$sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
        FROM expense_claims 
        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
        WHERE status != 'Reimbursed'";

$params = [];

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
if (!empty($_GET['status'])) {
    $sql .= " AND status = :status";
    $params[':status'] = $_GET['status'];
} else {
    $sql .= " AND status != 'Reimbursed'";
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
  <nav class="navbar">
    <div class="logo">FDM Expense App - Finance</div>
    <div class="nav-links">
      <form method="POST" action="../php/logout.php">
        <button class="Btn" type="submit">Logout</button>
      </form>
    </div>
  </nav>

  <section class="active-alerts">
    <h3>📋 All Claims (Finance View)</h3>
    <form method="GET">
      <label>Status:</label>
      <select name="status">
        <option value="">All</option>
        <option value="Pending" <?= ($_GET['status'] ?? '') == 'Pending' ? 'selected' : '' ?>>Pending</option>
        <option value="Approved" <?= ($_GET['status'] ?? '') == 'Approved' ? 'selected' : '' ?>>Approved</option>
        <option value="Rejected" <?= ($_GET['status'] ?? '') == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
        <option value="Reimbursed" <?= ($_GET['status'] ?? '') == 'Reimbursed' ? 'selected' : '' ?>>Reimbursed</option>
      </select>

      <label>Date:</label>
      <input type="date" name="date" value="<?= htmlspecialchars($_GET['date'] ?? '') ?>">

      <label>Category:</label>
      <select name="category">
        <option value="">All</option>
        <option value="Food">Food</option>
        <option value="Travel">Travel</option>
        <option value="Office Supplies">Office Supplies</option>
        <option value="Accommodation">Accommodation</option>
        <option value="Fuel">Fuel</option>
      </select>

      <label>Amount:</label>
      <input type="number" step="0.01" name="amount" value="<?= $_GET['amount'] ?? '' ?>">

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
    <form method="POST" action="../php/generate_report.php" target="_blank">
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
