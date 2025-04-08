<?php
session_start();
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.html");
    exit();
}
require_once "models/DatabaseManager.php";


$loggedInUsername = $_SESSION['username'] ?? '';
$loggedInRole = $_SESSION['role'] ?? '';
$loggedInId = $_SESSION['employeeId'] ?? null;


$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : "";


$conn = DatabaseManager::getInstance()->getConnection();
$params = [];
$sql = "SELECT employeeId, firstName, lastName, email, role, username FROM employees WHERE 1=1";


if (!empty($searchTerm)) {
    $sql .= " AND (username LIKE ? OR firstName LIKE ?)";
    $params[] = "%" . $searchTerm . "%";
    $params[] = "%" . $searchTerm . "%";
}


if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}


if ($loggedInRole === "Admin") {
    $sql .= " AND employeeId != ?";
    $params[] = $loggedInId;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


$roles = ['Employee', 'Manager', 'Admin', 'Finance'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>FDM Expense App - Admin Dashboard</title>
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

    <!-- User Table Section -->
    <section class="weather-map">
        <h3>System users</h3>

        <div class="tabs">
            <!-- Search and Filter Form -->
            <form method="GET" action="admin_dashboard.php" style="display: inline;">
                <input id="filter-text" type="text" name="search" placeholder="Search by username or first name..." value="<?= htmlspecialchars($searchTerm) ?>">

                <select id="filter-select" name="role">
                    <option value="">All Roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= $role ?>" <?= ($roleFilter === $role) ? 'selected' : '' ?>>
                            <?= $role ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Filter</button>
            </form>

            <!-- Add User Button -->
            <form method="GET" action="create_employee.php" style="display: inline;">
                <button type="submit">Add Employee</button>
            </form>
            <form>
                <button type="submit" formaction="create_manager.php">Add Manager</button>
            </form>
        </div>
        <br>

        <?php if (count($users) === 0): ?>
            <p>No users found.</p>
        <?php else: ?>
            <?php foreach ($users as $user): ?>
                <div class="report">
                    <h4><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?> 
                        <span style="font-weight: normal;">(<?= $user['role'] ?>)</span>
                    </h4>
                    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                    <p>Username: <?= htmlspecialchars($user['username']) ?></p>
                    <br>
                    <!-- Delete Button -->
                    <form method="POST" action="delete_user.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                        <input type="hidden" name="employeeId" value="<?= $user['employeeId'] ?>">
                        <button type="submit">Delete User</button>
                    </form>

                    <!-- Change Password Button -->
                    <form method="GET" action="change_password.php" style="display:inline;">
                        <input type="hidden" name="employeeId" value="<?= $user['employeeId'] ?>">
                        <button type="submit">Change Password</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <!-- System Logs Placeholder -->
    <section class="weather-map">
        <h3>System Logs</h3>
        <div class="tabs">
            <button>Search User</button>
            <button>Sort Oldest</button>
            <button>Sort Newest</button>
        </div>
        <br>
        <div class="map-placeholder">[System Log table placeholder]</div>
    </section>
</div>
</body>
</html>
