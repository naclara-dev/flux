<?php

namespace App\Models\Repositories;

use App\Models\Entities\Category;

class CategoryRepository extends Repository {
    protected $table = 'categories';
    protected $entityClass = Category::class;

    public function save(array $data) {
        if (!empty($data['id'])) {
            return $this->update($data);
        }

        return $this->create($data);
    }

    public function create(array $data) {
        $query = "INSERT INTO $this->table (user_id, name, color, icon) VALUES (:user_id, :name, :color, :icon)";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':color', $data['color']);
        $stmt->bindValue(':icon', $data['icon']);

        return $stmt->execute();
    }

    public function update(array $data) {
        $query = "UPDATE $this->table SET name = :name, color = :color, icon = :icon WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':id', $data['id']);
        $stmt->bindValue(':user_id', $data['user_id']);
        $stmt->bindValue(':name', $data['name']);
        $stmt->bindValue(':color', $data['color']);
        $stmt->bindValue(':icon', $data['icon']);

        return $stmt->execute();
    }
}
