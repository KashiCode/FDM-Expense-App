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

    // TODO: Implement processReimbursement method
    // TODO: Implement generateReport method
    // TODO: Implement searchEmployee method
}
?>
