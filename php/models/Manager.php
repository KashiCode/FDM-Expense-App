<?php
require_once "Employee.php"; // Import Employee class

class Manager extends Employee {
    
    public function __construct() {
        parent::__construct(); // Call Employee constructor to get DB connection
    }

    // Get the manager's team (list of employees under them)
    public function getTeam($managerId) {
        $sql = "SELECT * FROM employees WHERE manager = :managerId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':managerId', $managerId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Return employee list
    }

    // Get the spending limit of a manager
    public function getSpendingLimit($managerId) {
        $sql = "SELECT spendingLimit FROM managers WHERE managerId = :managerId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':managerId', $managerId);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    // Create a new manager (Insert into both employees and managers tables)
    public function createManager($firstName, $lastName, $email, $username, $password, $spendingLimit) {
        $this->conn->beginTransaction();

        try {
            // Hash password
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Insert into employees table
            $sql1 = "INSERT INTO employees (firstName, lastName, email, role, username, passwordHash) 
                        VALUES (:firstName, :lastName, :email, 'Manager', :username, :passwordHash)";
            $stmt1 = $this->conn->prepare($sql1);
            $stmt1->bindParam(':firstName', $firstName);
            $stmt1->bindParam(':lastName', $lastName);
            $stmt1->bindParam(':email', $email);
            $stmt1->bindParam(':username', $username);
            $stmt1->bindParam(':passwordHash', $passwordHash);
            $stmt1->execute();

            // Get the last inserted employeeID
            $managerId = $this->conn->lastInsertId();

            // Insert into managers table
            $sql2 = "INSERT INTO managers (managerId, spendingLimit) VALUES (:managerId, :spendingLimit)";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->bindParam(':managerId', $managerId);
            $stmt2->bindParam(':spendingLimit', $spendingLimit);
            $stmt2->execute();

            $sql3 = "UPDATE employees SET manager = $managerId WHERE employeeID = $managerId";
            $stmt3 = $this->conn->prepare($sql3);
            $stmt3->bindParam(':managerId', $managerId);
            $stmt3->execute();

            // Commit transaction
            $this->conn->commit();
            return true;

        } catch (Exception $e) {
            $this->conn->rollBack();
            return false;
        }
    }
}
?>
