<?php
session_start();
require_once "models/DatabaseManager.php";

if (!isset($_SESSION["employeeId"])) {
    header("Location: ../loginPage.php");
    exit();
}

$message = "";
$claim = null;

if (isset($_GET["claimId"])) {
    $claimId = $_GET["claimId"];
    $employeeId = $_SESSION["employeeId"];

    $conn = DatabaseManager::getInstance()->getConnection();
    $sql = "SELECT * FROM expense_claims WHERE claimId = ? AND employeeId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$claimId, $employeeId]);
    $claim = $stmt->fetch(PDO::FETCH_ASSOC);

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $amount = $_POST["amount"];
    $description = $_POST["description"];
    $category = $_POST["category"];
    $currency = $_POST["currency"];
    
    $evidenceFile = $claim["evidenceFile"];

    if (!empty($_FILES["evidenceFile"]["name"])) {
        $uploadDir = "uploads/";
        $fileType = strtolower(pathinfo($_FILES["evidenceFile"]["name"], PATHINFO_EXTENSION));
        $fileName = uniqid() . "_" . basename($_FILES["evidenceFile"]["name"]);
        $evidenceFile = $uploadDir . $fileName;

        if (!move_uploaded_file($_FILES["evidenceFile"]["tmp_name"], $evidenceFile)) {
            $message = "Error uploading file.";
        }
    }

    $username = $_SESSION['username'];
    $role = $_SESSION['role'];
    $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime)
            VALUES (:employeeId, (SELECT username FROM employees WHERE employeeId = :employeeId), :role, :event, NOW())";
    $event = $username . " Updated Claim " . $claimId;
    $stmt = $conn->prepare($sql);
    $stmt->execute([':employeeId' => $employeeId, ':event' => $event, ':role' => $role]);

    $sql = "UPDATE expense_claims SET amount = ?, description = ?, category = ?, currency = ?, evidenceFile = ?
            WHERE claimId = ? AND employeeId = ?";
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
    <title>Update Expense Claim</title>
    <link rel="stylesheet" href="../css/index.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        function previewImage(event) {
            const reader = new FileReader();
            reader.onload = function(){
                const output = document.getElementById('imagePreview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    </script>
</head>
<body>

<h1>Update Expense Claim</h1>

<?php if ($message): ?>
    <p id="signUpMessage"><?= $message; ?></p>
<?php endif; ?>

<form method="post" action="" enctype="multipart/form-data">
    <input type="hidden" name="claimId" value="<?= $claim['claimId'] ?>">

    <label for="amount">Amount:</label>
    <div class="currecy-amount">
        <select id="currency" name="currency" required>
            <option value="GBP" <?= ($claim['currency'] == 'GBP') ? 'selected' : '' ?>>GBP</option>
        </select>
        <input type="number" step="0.01" name="amount" value="<?= $claim['amount'] ?>" min="0.01" required>
    </div>

    <label for="description">Description:</label>
    <textarea name="description" required><?= htmlspecialchars($claim['description']) ?></textarea>

    <label for="category">Category:</label>
    <select name="category" required>
        <option value="Travel" <?= ($claim['category'] == 'Travel') ? 'selected' : '' ?>>Travel</option>
        <option value="Food" <?= ($claim['category'] == 'Food') ? 'selected' : '' ?>>Food</option>
        <option value="Office Supplies" <?= ($claim['category'] == 'Office Supplies') ? 'selected' : '' ?>>Office Supplies</option>
        <option value="Accommodation" <?= ($claim['category'] == 'Accommodation') ? 'selected' : '' ?>>Accommodation</option>
        <option value="Fuel" <?= ($claim['category'] == 'Fuel') ? 'selected' : '' ?>>Fuel</option>
    </select>

    <label for="evidenceFile">Upload New Evidence:</label>
    <label class="file-upload" for="image">
        <input type="file" id="image" name="evidenceFile" accept="image/*" onchange="previewImage(event)">
        Choose Image
    </label>

    <img id="imagePreview"
         src="<?= (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])) ? $claim['evidenceFile'] : '' ?>"
         style="<?= (!empty($claim['evidenceFile']) && file_exists($claim['evidenceFile'])) ? 'display:block' : 'display:none' ?>; max-width: 300px; margin-top: 10px;">

    <button type="submit">Update Claim</button>
</form>

<p></p>
<div class="home-button">
    <a href="employee_dashboard.php">Homepage</a>
</div>

</body>
</html>
