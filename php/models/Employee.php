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
}
?>
