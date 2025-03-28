<?php
namespace Config;

class Database {
    private static $instance = null;
    private $conn;

    private $host = 'localhost';
    private $user = 'root';
    private $pass = '';
    private $name = 'solana_affiliate';

    private function __construct() {
        try {
            $this->conn = new \mysqli($this->host, $this->user, $this->pass, $this->name);
            if ($this->conn->connect_error) {
                throw new \Exception("Kết nối MySQL thất bại: " . $this->conn->connect_error);
            }
            $this->conn->set_charset("utf8mb4");
        } catch (\Exception $e) {
            die("Lỗi: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function close() {
        if ($this->conn) {
            $this->conn->close();
            self::$instance = null;
        }
    }
}