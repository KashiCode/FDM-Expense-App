<?php
require_once "Employee.php";

class Admin extends Employee {
    public function __construct() {
        parent::__construct();
    }

    // The functions createUser and updateUser will be used in the Admin dashboard, no need to have it here.

    public function deleteEmployee($employeeID) {
        $sql = "DELETE FROM employees WHERE employeeID = :employeeID";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':employeeID', $employeeID);
        return $stmt->execute();
    }

    public function updateSpendingLimit($managerId, $newLimit) {
        $sql = "UPDATE managers SET spendingLimit = :newLimit WHERE managerId = :managerId";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':managerId', $managerId);
        $stmt->bindParam(':newLimit', $newLimit);
        return $stmt->execute();
    }

    // TODO: Implement the viewLogs function
}   
?>
