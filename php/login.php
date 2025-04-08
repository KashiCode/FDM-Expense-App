<?php

// Start session
session_start();

// Include database connection
require_once 'models/DatabaseManager.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION["errorMessage"] = "Username and password are required.";
        exit;
    }

    // Prepare SQL query to fetch user details
    $conn = DatabaseManager::getInstance()->getConnection();
    $sql = "SELECT * FROM employees WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(1, $username); // Use numeric index for PDO
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC); // Fetch as associative array

    if ($user) { // Check if user exists (no need for num_rows)
        // Verify password
        if (password_verify($password, $user['passwordHash'])) {
            // Store user data in session
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['employeeId'] = $user['employeeId'];
            

            // Update loggedIn timestamp
            $setLoggedIn = "UPDATE employees SET loggedIn = NOW() WHERE username = ?";
            $stmt2 = $conn->prepare($setLoggedIn);
            $stmt2->bindParam(1, $username); // Use numeric index for PDO
            $stmt2->execute();

            // Redirect based on role
            switch ($user['role']) {
                case 'Manager':
                    header("Location: manager_dashboard.php");
                    break;
                case 'Employee':
                    header("Location: employee_dashboard.php");
                    break;
                case 'Admin':
                    header("Location: admin_dashboard.php");
                    break;
                case 'Finance':
                    header("Location: finance_dashboard.php");
                    break;
                default:
                    $_SESSION["errorMessage"] = "Invalid role. {$user['role']}";
                    break;
            }
            exit;
        } else {
            echo "Invalid password.";
        }
    } else {
        $_SESSION["errorMessage"] = "User not found.";
    }
} else {
    $_SESSION["errorMessage"] = "Invalid request method.";
}
header("Location: ../loginPage.php");
exit();
?>