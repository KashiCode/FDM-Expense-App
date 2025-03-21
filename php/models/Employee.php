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

    // TODO: Implement the createClaim function
    // TODO: Implement the uploadClaimEvidence function (might not need it)
    // TODO: Implement the viewClaim function
    // TODO: Implement the editClaim function
    // TODO: Implement the deleteClaim function
    // TODO: Implement the searchClaim function
    // TODO: Implement the logout function (the loggedIn attribute should be of type dateTime and something should check if the user was logged in for more than a day)
}
?>
