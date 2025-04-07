<?php
session_start();
if (!isset($_SESSION['employeeId']) || !in_array($_SESSION['role'], ['Manager', 'Finance'])) {
    header("Location: ../login.html");
    exit();
}

require_once "models/DatabaseManager.php";

if (!isset($_GET['id'])) {
    echo "Claim ID is not provided.";
    exit();
}

$conn = DatabaseManager::getInstance()->getConnection();
$claimId = $_GET['id'];

$sql = "SELECT expense_claims.*, employees.firstName, employees.lastName 
        FROM expense_claims 
        INNER JOIN employees ON expense_claims.employeeId = employees.employeeId 
        WHERE expense_claims.claimId = :claimId";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':claimId', $claimId, PDO::PARAM_INT);
$stmt->execute();

$claim = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Claim</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <div class="logo">FDM Expense App</div>
            <div class="nav-links">
                <form method="POST" action="../php/logout.php">
                    <button class="Btn" type="submit">
                        <div class="text">Logout</div>
                    </button>
                </form>
            </div>
        </nav>
        <br>
        <section class="weather-map">
            <?php if ($claim): ?>
                <h1 style='text-align:center;'>📝 Claim #<?= $claim['claimId'] ?></h1>
                <div class='report' style='text-align: center;'>
                    <h4>Employee: <?= htmlspecialchars($claim['firstName'] . ' ' . $claim['lastName']) ?></h4>
                    <h3>Amount: <?= $claim['currency'] . ' ' . number_format($claim['amount'], 2) ?></h3>
                    <p><strong>Date Submitted:</strong> <?= date("d/m/Y", strtotime($claim['date'])) ?></p>
                    <p><strong>Category:</strong> <?= htmlspecialchars($claim['category']) ?></p>
                    <p><strong>Description:</strong> <?= htmlspecialchars($claim['description']) ?></p>

                    <?php if (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])): ?>
                        <h3>Evidence:</h3>
                        <img src="<?= $claim['evidenceFile'] ?>" alt="Claim Evidence" style="width: 60%; border-radius: 10px; margin: 10px;">
                    <?php endif; ?>

                    <br>

                    <?php if ($claim['status'] === 'Pending'): ?>
                        <div class='badges'><button class='blue'>Pending Approval</button></div><br>
                        <a href='approve_claim.php?id=<?= $claim['claimId'] ?>'>
                            <button style='margin-right: 10px;'>Accept Claim</button>
                        </a>
                        <a href='reject_claim.php?id=<?= $claim['claimId'] ?>'>
                            <button>Reject Claim</button>
                        </a>
                    <?php elseif ($claim['status'] === 'Rejected'): ?>
                        <div class='badges'><button class='red'>Rejected</button></div>
                    <?php elseif ($claim['status'] === 'Approved'): ?>
                        <div class='badges'><button class='green'>Approved</button></div>
                    <?php elseif ($claim['status'] === 'Reimbursed'): ?>
                        <div class='badges'><button class='purple'>Reimbursed</button></div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center;">Claim not found or you do not have permission to view it.</p>
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
