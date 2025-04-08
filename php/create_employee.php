<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../loginPage.php");
    exit();
}
require_once "models/Employee.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $role = $_POST["role"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $manager = !empty($_POST["manager"]) ? $_POST["manager"] : null;

    $employee = new Employee();
    if ($employee->createEmployee($firstName, $lastName, $email, $role, $username, $password, $manager)) {
        $message = "Employee created successfully!";
        header("Location: create_employee.php");
        exit();
    } else {
        $message = "Error creating employee.";
    }
}

$conn = DatabaseManager::getInstance()->getConnection();

$sql = "SELECT e.employeeId, e.firstName, e.lastName 
        FROM employees e 
        INNER JOIN managers m ON e.employeeId = m.managerId";

$stmt = $conn->prepare($sql);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Employee</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <h1>Create Employee</h1>
    
    <?php if ($message): ?>
        <p id="signUpMessage"><?php echo $message; ?></p>
    <?php endif; ?>
    
    <form method="post" action="">
        <label for="firstName">First Name:</label>
        <input type="text" name="firstName" required>
        
        <label for="lastName">Last Name:</label>
        <input type="text" name="lastName" required>
        
        <label for="email">Email:</label>
        <input type="email" name="email" required>
        
        <label for="role">Role:</label>
        <select name="role" required>
            <option value="Employee">Employee</option>
            <option value="Admin">Admin</option>
            <option value="Finance">Finance</option>
        </select>
        
        <label for="username">Username:</label>
        <input type="text" name="username" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" required>
        
        <label for="manager">Manager:</label>
        <select name="manager" required>
            <option value="">None</option>
            <?php while ($row = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                <option value="<?php echo $row['employeeId']; ?>">
                    <?php echo $row['firstName'] . " " . $row['lastName']; ?>
                </option>
            <?php endwhile; ?>
        </select> 

        <button type="submit">Create Employee</button>
    </form>
</body>
</html>
