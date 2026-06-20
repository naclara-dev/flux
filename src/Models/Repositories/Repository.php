<?php

namespace App\Models\Repositories;

use App\Core\Database;
use App\Core\Session;

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

    private function getFields(): array {
        $reflection = new \ReflectionClass($this->entityClass);

        $fields = [];

        foreach ($reflection->getProperties() as $property) {
            $name = $property->getName();

            if ($name !== 'id') {
                $fields[] = camelToSnake($name);
            }            
        }

        return $fields;        
    }

    private function getParamName(string $field): string {
        return str_replace('.', '_', $field);
    }

    private function getParams(array $fields, bool $isUpdate = false, string $separator = ','): string {
        $params = [];

        foreach ($fields as $f) {
            $param = $this->getParamName($f);
            $params[] = $isUpdate ? "$f = :$param" : ":$param";
        }

        return implode($separator, $params);
    }     

    public function all($filters = [], $joins = [], $columns = '*'): array {
        $fields = array_keys($filters);
        $query = "SELECT $columns FROM $this->table";

        foreach ($joins as $join) {
            $query .= " $join";
        }
        
        if (!empty($filters)) {
            $query .= " WHERE " . $this->getParams($fields, true, ' AND ');
        }

        $stmt = $this->db->prepare($query);

        foreach ($filters as $field => $value) {
            $stmt->bindValue(":" . $this->getParamName($field), $value);
        }

        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function allFromUser(): array {
        return $this->all([
            "user_id" => Session::get("user_id")
        ]);
    }

    public function count(array $filters = []): int {
        $fields = array_keys($filters);
        $query = "SELECT COUNT(id) FROM $this->table";

        if (!empty($filters)) {
            $query .= " WHERE " . $this->getParams($fields, true, ' AND ');
        }

        $stmt = $this->db->prepare($query);

        foreach ($filters as $field => $value) {
            $stmt->bindValue(":" . $this->getParamName($field), $value);
        }

        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function countFromUser(): int {
        return $this->count([
            "user_id" => Session::get("user_id")
        ]);        
    }

    public function find(array $filters = []) {
        if (empty($filters)) {
            return null;
        }

        $filter = $this->getParams(array_keys($filters), true, ' AND ');
        $query = "SELECT * FROM $this->table WHERE $filter LIMIT 1";
        $stmt = $this->db->prepare($query);

        foreach ($filters as $field => $value) {
            $stmt->bindValue(":" . $this->getParamName($field), $value);
        }

        $stmt->execute();

        return $stmt->fetch();
    }

    public function save(array $data) {
        $hasID = !empty($data['id']) && $this->find([
            'id' => $data['id']
        ]);

        if ($hasID) {
            return $this->update($data);
        } else {
            return $this->insert($data);
        }        
    }

    private function update(array $data) {
        $fields = $this->getFields();
        $updateFields = $fields;

        if (array_key_exists('user_id', $data)) {
            $updateFields = array_values(array_filter($fields, function ($field) {
                return $field !== 'user_id';
            }));
        }

        $sets = $this->getParams($updateFields, true);

        $query = "UPDATE $this->table SET $sets WHERE id = :id";

        if (array_key_exists('user_id', $data)) {
            $query .= " AND user_id = :user_id";
        }

        $stmt = $this->db->prepare($query);

        foreach ($updateFields as $f) {
            $stmt->bindValue(":$f", $data[$f]);
        }

        $stmt->bindValue(':id', $data['id']);

        if (array_key_exists('user_id', $data)) {
            $stmt->bindValue(':user_id', $data['user_id']);
        }

        return $stmt->execute();
    }

    private function insert(array $data) {
        $fields = $this->getFields();
        $params = $this->getParams($fields);
        $columns = implode(',', $fields);

        $query = "INSERT INTO $this->table ($columns) VALUES ($params)";
        $stmt = $this->db->prepare($query);

        foreach ($fields as $f) {
            $stmt->bindValue(":$f", $data[$f]);
        }

        if (!$stmt->execute()) {
            return false;
        }

        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id, array $filters = []) {
        $query = "DELETE FROM $this->table WHERE id = :id";

        if (!empty($filters)) {
            $query .= " AND " . $this->getParams(array_keys($filters), true, ' AND ');
        }

        $stmt = $this->db->prepare($query);
        $stmt->bindValue(":id", $id);

        foreach ($filters as $field => $value) {
            $stmt->bindValue(":" . $this->getParamName($field), $value);
        }

        return $stmt->execute();
    }
}
