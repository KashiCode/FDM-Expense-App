<?php
session_start();
require_once "models/DatabaseManager.php"; // Ensure this is your database manager class

// Ensure user is logged in
if (!isset($_SESSION["employeeId"])) {
    header("Location: ../login.html");
    exit();
}

$message = "";
$claim = null;

// Fetch claim details directly from the database
if (isset($_GET["claimId"])) {
    $claimId = $_GET["claimId"];
    $employeeId = $_SESSION["employeeId"];

    // Database query to fetch the claim by claimId and employeeId
    $conn = DatabaseManager::getInstance()->getConnection();
    $sql = "SELECT * FROM expense_claims WHERE claimId = ? AND employeeId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$claimId, $employeeId]);
    $claim = $stmt->fetch(PDO::FETCH_ASSOC);

    // If no claim is found, redirect with an error message
    if (!$claim) {
        $_SESSION["message"] = "Invalid claim ID.";
        header("Location: employee_dashboard.php");
        exit();
    }
} else {
    $_SESSION["message"] = "No claim ID provided.";
    header("Location: employee_dashboard.php");
    exit();
}

// Handling the form submission to update the claim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $currency = $_POST["currency"];
    
    $evidenceFile = $claim["evidenceFile"]; // Keep old file if no new one is uploaded

    if (!empty($_FILES["evidenceFile"]["name"])) {
        $uploadDir = "uploads/";
        $fileType = strtolower(pathinfo($_FILES["evidenceFile"]["name"], PATHINFO_EXTENSION));
        $fileName = uniqid() . "_" . basename($_FILES["evidenceFile"]["name"]);
        $evidenceFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES["evidenceFile"]["tmp_name"], $evidenceFile)) {
            $message = "Error uploading file.";
        }
    }

    // Update claim in the database
    $sql = "UPDATE expense_claims SET amount = ?, description = ?, category = ?, currency = ?, evidenceFile = ? WHERE claimId = ? AND employeeId = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$amount, $description, $category, $currency, $evidenceFile, $claimId, $employeeId]);

    if ($result) {
        $_SESSION["message"] = "Expense claim updated successfully!";
        header("Location: employee_dashboard.php");
        exit();
    } else {
        $message = "Error updating claim.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Expense Claim</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
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
        
        <section class="weather-map">
            <h1>Update Expense Claim</h1>

            <?php if ($message): ?>
                <p id="updateMessage" style="color: red;"><?= $message; ?></p>
            <?php endif; ?>
            <form method="post" action="" enctype="multipart/form-data" class="report">
                <input type="hidden" name="claimId" value="<?= $claim['claimId'] ?>" required>
                <br>
                <label for="amount">Amount:</label>
        
                <div class="currency-amount">
                    <select id="currency" name="currency" required>
                        <option value="USD" <?= ($claim['currency'] == 'USD') ? 'selected' : '' ?>>USD</option>
                        <option value="EUR" <?= ($claim['currency'] == 'EUR') ? 'selected' : '' ?>>EUR</option>
                        <option value="GBP" <?= ($claim['currency'] == 'GBP') ? 'selected' : '' ?>>GBP</option>
                        <option value="JPY" <?= ($claim['currency'] == 'JPY') ? 'selected' : '' ?>>JPY</option>
                        <option value="AUD" <?= ($claim['currency'] == 'AUD') ? 'selected' : '' ?>>AUD</option>
                    </select>
                    <input type="number" step="0.01" name="amount" value="<?= $claim['amount'] ?>" required>
                </div>
                <br>
                <label for="description">Description:</label>
                <br>
                <textarea name="description" required><?= htmlspecialchars($claim['description']) ?></textarea>
                <br>
                <label for="category">Category:</label>
                <br>
                <select name="category" required>
                    <option value="Travel" <?= ($claim['category'] == 'Travel') ? 'selected' : '' ?>>Travel</option>
                    <option value="Food" <?= ($claim['category'] == 'Food') ? 'selected' : '' ?>>Food</option>
                    <option value="Office Supplies" <?= ($claim['category'] == 'Office Supplies') ? 'selected' : '' ?>>Office Supplies</option>
                    <option value="Accommodation" <?= ($claim['category'] == 'Accommodation') ? 'selected' : '' ?>>Accommodation</option>
                    <option value="Fuel" <?= ($claim['category'] == 'Fuel') ? 'selected' : '' ?>>Fuel</option>
                </select>
                <br>
                <label for="evidenceFile">Upload New Evidence (Optional):</label>
                <br>
                <input type="file" name="evidenceFile" accept="image/*">
                <br>
                <?php if (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])): ?>
                    <br>
                    <img src="<?= $claim['evidenceFile'] ?>" alt="Current Receipt">
                <?php endif; ?>
                <br>
                <button type="submit">Update Claim</button>
            </form>
        </section>

        <div class="home-button">
            <a href="employee_dashboard.php">Back to Home</a>
        </div>

    </div>
</body>
</html>
