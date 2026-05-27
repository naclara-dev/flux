<?php

namespace App\Core;

class Database
{
    private static $instance;
    private $conn;

    private function __construct()
    {
        $dbhost   = $_ENV['DB_HOST'];
        $dbname   = $_ENV['DB_NAME'];
        $dbuser   = $_ENV['DB_USER'];
        $dbpass   = $_ENV['DB_PASS'];

        $dsn = "mysql:host=$dbhost;dbname=$dbname;charset=utf8mb4";

        $options = [
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        $this->conn = new \PDO($dsn, $dbuser, $dbpass, $options);
    }

    public static function getInstance(): Database {
        if (self::$instance == null) {
            self::$instance = new Database();
        }

        return self::$instance;
    }

    public function getConnection(): \PDO {
        return $this->conn;
    }
}