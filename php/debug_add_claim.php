<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['employeeId'])) {
    echo json_encode(["success" => false, "message" => "Not logged in."]);
    exit;
}

require_once "../models/DatabaseManager.php";

$conn = DatabaseManager::getInstance()->getConnection();

try {
    $stmt = $conn->prepare("INSERT INTO expense_claims 
        (employeeId, amount, description, category, status, evidenceFile, receipt, currency) 
        VALUES 
        (:employeeId, :amount, :description, :category, 'Approved', :evidenceFile, '', :currency)");

    $stmt->execute([
        ':employeeId' => $_SESSION['employeeId'],
        ':amount' => rand(10, 100),
        ':description' => 'Debug Auto Claim',
        ':category' => 'Food',
        ':evidenceFile' => 'uploads/debug.jpg',
        ':currency' => 'GBP'
    ]);

    echo json_encode(["success" => true, "message" => "Test claim added!"]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
