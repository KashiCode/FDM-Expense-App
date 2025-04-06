<?php
require_once "Employee.php";

class Finance extends Employee {
    public function __construct() {
        parent::__construct();
    }

    public function getTeam($financeID) {
        $sql = "SELECT * FROM employees WHERE manager = :financeID";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':financeID', $financeID);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function setAttributes($data) {
        $this->employeeId = $data['employeeId'];
        $this->firstName = $data['firstName'];
        $this->lastName = $data['lastName'];
        $this->email = $data['email'];
        $this->role = $data['role'];
        $this->username = $data['username'];
    }

    // Process a reimbursement by updating the claim status and adding notes
    public function processReimbursement($employeeId, $amount) {
        // Check if the employee exists
        $sql = "SELECT * FROM employees WHERE employeeId = :employeeId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->execute();
        
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Employee not found.'];
        }
    
        // Now pretend we're reimbursing the employee (you can replace this with real logic)
        // Example: inserting into a 'reimbursements' table
        $sql = "INSERT INTO reimbursements (employeeId, amount, status, processed_at) VALUES (:employeeId, :amount, 'processed', NOW())";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':amount', $amount);
    
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Reimbursement processed.'];
        } else {
            return ['success' => false, 'message' => 'Database error while processing reimbursement.'];
        }
    }
    

    //  Generate a summary report of all claims, optionally filtered by status
    public function generateReport($status = null) {
        if ($status) {
            $sql = "SELECT * FROM claims WHERE status = :status ORDER BY date_submitted DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':status', $status);
        } else {
            $sql = "SELECT * FROM claims ORDER BY date_submitted DESC";
            $stmt = $this->conn->prepare($sql);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Search for employees by name or email
    public function searchEmployee($query) {
        $searchTerm = "%" . $query . "%";
        $sql = "SELECT * FROM employees WHERE firstName LIKE :query OR lastName LIKE :query OR email LIKE :query";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':query', $searchTerm);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
