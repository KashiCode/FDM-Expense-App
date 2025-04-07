<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.html");
    exit();
}
require_once "models/Manager.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = $_POST["firstName"];
    $lastName = $_POST["lastName"];
    $email = $_POST["email"];
    $username = $_POST["username"];
    $password = $_POST["password"];
    $spendingLimit = $_POST["spendingLimit"];

    $manager = new Manager();
    if ($manager->createManager($firstName, $lastName, $email, $username, $password, $spendingLimit)) {
        $message = "Manager created successfully!";
        header("Location: create_manager.php");
        exit();
    } else {
        $message = "Error creating manager.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Manager</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <h1>Create Manager</h1>

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

        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <label for="spendingLimit">Spending Limit:</label>
        <input type="number" step="0.01" name="spendingLimit" required>

        <button type="submit">Create Manager</button>
    </form>
</body>
</html>
