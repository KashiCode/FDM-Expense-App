<?php
class DatabaseManager {
    private static $instance = null;
    private $conn;

    private $host = "localhost";
    private $db_name = "fdm_expenses";
    private $username = "root";
    private $password = "";

    private function __construct() {
        try {
            $this->conn = new PDO("mysql:host=$this->host;dbname=$this->db_name", $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new DatabaseManager();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
