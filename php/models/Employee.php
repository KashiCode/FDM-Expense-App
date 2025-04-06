<?php
require_once "DatabaseManager.php";

class Employee {
    public $conn;

    public function __construct() {
        $this->conn = DatabaseManager::getInstance()->getConnection();
    }

    public function createEmployee($firstName, $lastName, $email, $role, $username, $password, $manager) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $sql = "INSERT INTO employees (firstName, lastName, email, role, username, passwordHash, manager) 
                VALUES (:firstName, :lastName, :email, :role, :username, :passwordHash, :manager)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':firstName', $firstName);
        $stmt->bindParam(':lastName', $lastName);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':role', $role);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':passwordHash', $passwordHash);
        $stmt->bindParam(':manager', $manager);
        
        return $stmt->execute();
    }

    public function createClaim($employeeId, $amount, $description, $category) {
        $sql = "INSERT INTO claims (employeeId, amount, description, category, status, created_at)
                VALUES (:employeeId, :amount, :description, :category, 'Pending', NOW())";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
    
        return $stmt->execute();
    }

    public function viewClaims($employeeId) {
        $sql = "SELECT * FROM claims WHERE employeeId = :employeeId ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function editClaim($claimId, $employeeId, $amount, $description, $category) {
        // Make sure only the claim owner can edit and only if it's still pending
        $sql = "UPDATE claims 
                SET amount = :amount, description = :description, category = :category 
                WHERE id = :claimId AND employeeId = :employeeId AND status = 'Pending'";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':amount', $amount);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':category', $category);
        $stmt->bindParam(':claimId', $claimId);
        $stmt->bindParam(':employeeId', $employeeId);
    
        return $stmt->execute();
    }

    public function deleteClaim($claimId, $employeeId) {
        $sql = "DELETE FROM claims WHERE id = :claimId AND employeeId = :employeeId AND status = 'Pending'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':claimId', $claimId);
        $stmt->bindParam(':employeeId', $employeeId);
    
        return $stmt->execute();
    }
    
    public function searchClaim($employeeId, $searchTerm) {
        $searchTerm = "%" . $searchTerm . "%";
        $sql = "SELECT * FROM claims 
                WHERE employeeId = :employeeId 
                  AND (description LIKE :search OR category LIKE :search)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeId', $employeeId);
        $stmt->bindParam(':search', $searchTerm);
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
}
?>
