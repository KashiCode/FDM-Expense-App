<?php
require_once "models/Employee.php";
require_once "models/Manager.php";
require_once "models/DatabaseManager.php";

$message = "";


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST["firstName"]);
    $lastName = trim($_POST["lastName"]);
    $email = trim($_POST["email"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $role = $_POST["role"];
    $manager = !empty($_POST["manager"]) ? $_POST["manager"] : null;
    $spendingLimit = isset($_POST["spendingLimit"]) ? $_POST["spendingLimit"] : null;

  
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $employee = new Employee();

    if ($employee->createEmployee($firstName, $lastName, $email, $role, $username, $passwordHash, $manager)) {
        
        if ($role === "Manager" && !empty($spendingLimit)) {
            $db = DatabaseManager::getInstance()->getConnection();
            $lastId = $db->lastInsertId();
            $stmt = $db->prepare("INSERT INTO managers (managerId, spendingLimit) VALUES (?, ?)");
            $stmt->execute([$lastId, $spendingLimit]);
        }

        $message = "User created successfully!";
        header("Location: admin_dashboard.php");
        exit();
    } else {
        $message = "Error creating user.";
    }
}


$conn = DatabaseManager::getInstance()->getConnection();
$stmt = $conn->query("SELECT e.employeeId, e.firstName, e.lastName 
                      FROM employees e 
                      INNER JOIN managers m ON e.employeeId = m.managerId");
$managers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create User</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <h1>Create User</h1>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="firstName">First Name:</label>
        <input type="text" name="firstName" required>

        <label for="lastName">Last Name:</label>
        <input type="text" name="lastName" required>

        <label for="email">Email:</label>
        <input type="email" name="email" required>

        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="role">Role:</label>
        <select name="role" id="roleSelect" required onchange="toggleFields()">
            <option value="Employee">Employee</option>
            <option value="Manager">Manager</option>
            <option value="Finance">Finance</option>
        </select>

        <div id="managerField">
            <label for="manager">Assign Manager (Employees only):</label>
            <select name="manager">
                <option value="">None</option>
                <?php foreach ($managers as $m): ?>
                    <option value="<?= $m['employeeId'] ?>">
                        <?= htmlspecialchars($m['firstName'] . " " . $m['lastName']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="spendingLimitField" style="display: none;">
            <label for="spendingLimit">Spending Limit (Managers only):</label>
            <input type="number" step="0.01" name="spendingLimit">
        </div>

        <button type="submit">Create</button>
    </form>

    </p>
    <div class="home-button">
    <a href="admin_dashboard.php">Homepage</a>
</div>

    <script>
        function toggleFields() {
            const role = document.getElementById("roleSelect").value;
            document.getElementById("managerField").style.display = (role === "Employee") ? "block" : "none";
            document.getElementById("spendingLimitField").style.display = (role === "Manager") ? "block" : "none";
        }
        toggleFields();
    </script>
</body>
</html>
