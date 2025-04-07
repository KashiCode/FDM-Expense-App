<?php
session_start();
require_once "models/DatabaseManager.php";

// Get logged-in user info
$loggedInUsername = $_SESSION['username'] ?? '';
$loggedInRole = $_SESSION['role'] ?? '';
$loggedInId = $_SESSION['employeeId'] ?? null;

// Get search and role filter inputs
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : "";
$roleFilter = isset($_GET['role']) ? trim($_GET['role']) : "";

// Build SQL
$conn = DatabaseManager::getInstance()->getConnection();
$params = [];
$sql = "SELECT employeeId, firstName, lastName, email, role, username FROM employees WHERE 1=1";

// Search filter
if (!empty($searchTerm)) {
    $sql .= " AND (username LIKE ? OR firstName LIKE ?)";
    $params[] = "%" . $searchTerm . "%";
    $params[] = "%" . $searchTerm . "%";
}

// Role filter
if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

// Exclude logged-in admin from user list
if ($loggedInRole === "Admin") {
    $sql .= " AND employeeId != ?";
    $params[] = $loggedInId;
}

$sql .= " ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Role options for filter
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
                        <svg viewBox="0 0 512 512"><path d="..."></path></svg>
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
                <input type="text" name="search" placeholder="Search by username or first name..." value="<?= htmlspecialchars($searchTerm) ?>">

                <select name="role">
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
            <form method="GET" action="create_user.php" style="display: inline;">
                <button type="submit">Add User</button>
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
