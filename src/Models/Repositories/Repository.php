<?php

namespace App\Models\Repositories;

use App\Core\Database;

abstract class Repository {
    protected $db;
    protected $table = '';
    protected $entityClass = '';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getConnection(): \PDO {
        return $this->db;
    }

    public function getTable(): string {
        return $this->table;
    }

    public function getEntityClass(): string {
        return $this->entityClass;
    }

    public function listAll() {
        $query = "SELECT * FROM $this->table";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
