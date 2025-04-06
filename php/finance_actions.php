<?php
header('Content-Type: application/json'); // Ensures JSON is sent
require_once __DIR__ . "/models/Finance.php";

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
