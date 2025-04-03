<?php
require_once "DatabaseManager.php";

class ExpenseClaim {
    private $conn;

    

    public function __construct() {
        $this->conn = DatabaseManager::getInstance()->getConnection();
    }

    // Create a new expense claim
    public function createClaim($employeeId, $amount, $description, $category, $evidenceFile, $receipt, $currency) {

        $sql = "INSERT INTO expense_claims (employeeId, amount, description, category, evidenceFile, receipt, currency) 
                VALUES (:employeeId, :amount, :description, :category, :evidenceFile, :receipt, :currency)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':evidenceFile', $evidenceFile);
        // $stmt->bindParam(':receipt', $receipt);
        $stmt->bindParam(':currency', $currency);
        
        return $stmt->execute();
    }
}
?>
