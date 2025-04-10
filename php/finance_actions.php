<?php
session_start(); 
if (!isset($_SESSION['employeeId']) || $_SESSION['role'] != 'Finance') {
    header("Location: ../loginPage.php");
    exit;
}
$financeId = $_SESSION['employeeId'] ?? null;
if (!$financeId) {
    http_response_code(403);
    echo "Unauthorized access.";
    exit;
}
header('Content-Type: application/json'); // Ensures JSON is sent
require_once __DIR__ . "/models/Finance.php";
require_once __DIR__ . "/models/DatabaseManager.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['claimId'], $data['amount'])) {
    echo json_encode(["success" => false, "message" => "Missing claimId or amount."]);
    exit;
}

try {
    $conn = DatabaseManager::getInstance()->getConnection();

    // here i am updating the claim statuses to reimbursed
    $sql = "UPDATE expense_claims SET status = 'Reimbursed' WHERE claimId = :claimId AND status = 'Approved'";
    $statement = $conn ->prepare($sql);
    $statement -> execute([':claimId' => $data['claimId']]);

    $username = $_SESSION['username'];
    // Add the event to the system log.
    $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:financeId, (SELECT username FROM employees WHERE employeeId = :financeId), 'Finance', :event, NOW())";
    $event = $username . " Processed Reimbursement on Claim " . $data['claimId'];
    $stmt = $conn->prepare($sql);
    $stmt->execute([':financeId' => $financeId, ':event' => $event]);

    echo json_encode([
        "success" => true,
        "message" => "Reimbursement processed.",
        "data" => [
            "claimId" => $data['claimId'],
            "amount" => $data['amount']
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
