<?php
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
    $sql = "UPDATE expense_claims SET status = 'Reiumbursed' WHERE claimId = :claimId AND status = 'Approved'";
    $statement = $conn ->prepare($sql);
    $statement -> execute([':claimId' => $data['claimId']]);

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
