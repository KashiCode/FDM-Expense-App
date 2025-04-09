<?php
require_once "DatabaseManager.php";

class ExpenseClaim {
    private $conn;

    

    public function __construct() {
        $this->conn = DatabaseManager::getInstance()->getConnection();
    }

    // Create a new expense claim
    public function createClaim($employeeId, $amount, $description, $category, $evidenceFile, $receipt, $currency) {

        $sql = "INSERT INTO expense_claims (employeeId, amount, description, category, evidenceFile, currency) 
                VALUES (:employeeId, :amount, :description, :category, :evidenceFile, :currency)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':evidenceFile', $evidenceFile);
        // $stmt->bindParam(':receipt', $receipt);
        $stmt->bindParam(':currency', $currency);

        $this->logClaim($employeeId);
        
        return $stmt->execute();
    }

    public function logClaim($employeeId) {
        $username = $_SESSION['username'];
        $role = $_SESSION['role'];
        $sql = "INSERT INTO sys_log (employeeId, username, role, event, eventTime) VALUES (:employeeId, (SELECT username FROM employees WHERE employeeId = :employeeId), :role, :event, NOW())";
        $event = $username . " Created A New Claim";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':employeeId' => $employeeId, ':event' => $event, ':role' => $role]);
    }
}
?>
