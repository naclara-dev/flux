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

    public function getFields(): array {
        $reflection = new \ReflectionClass($this->entityClass);

        $fields = [];

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            if ($name !== 'id') {
                $fields[] = $name;
            }            
        }

        return $fields;        
    }

    public function getParams(array $fields, bool $isUpdate = false): string {
        $params = [];

        foreach ($fields as $f) {
            $params[] = $isUpdate ? "$f = :$f" : ":$f";          
        }

        return implode(',', $params);        
    }

    public function all() {
        $query = "SELECT * FROM $this->table";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function find($value, string $key = 'id') {
        $filter = $this->getParams([$key], true);
        $query = "SELECT * FROM $this->table WHERE $filter";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":$key", $value);
        $stmt->execute();

        return $stmt->fetch();
    }

    public function save(array $data) {
        $hasID = !empty($data['id']) && $this->find($data['id']);

        if ($hasID) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }        
    }

    public function update(array $data) {
        $fields = $this->getFields();
        $sets = $this->getParams($fields, true);

        $query = "UPDATE $this->table SET $sets WHERE id = :id";
        $stmt = $this->db->prepare($query);

        foreach ($fields as $f) {
            $stmt->bindValue(":$f", $data[$f]);
        }

        $stmt->bindValue(':id', $data['id']);

        return $stmt->execute();
    }

    public function insert(array $data) {
        $fields = $this->getFields();
        $params = $this->getParams($fields);
        $columns = implode(',', $fields);

        $query = "INSERT INTO $this->table ($columns) VALUES ($params)";
        $stmt = $this->db->prepare($query);

        foreach ($fields as $f) {
            $stmt->bindValue(":$f", $data[$f]);
        }

        return $stmt->execute();        
    }
}
