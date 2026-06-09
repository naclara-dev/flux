<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\EntityRepository;

class EntityController extends Controller {
    public function find() {
        $this->requireAuth();

        $id = (int) ($_GET["id"] ?? 0);
        $repository = new EntityRepository;

        $entity = $repository->find([
            "id" => $id,
            "user_id" => Session::get('user_id')
        ]);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($entity);
    }

    public function store() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('manage/entities');
            exit;
        }

        $this->requireAuth();

        $data = $this->normalizeData($_POST);
        
        $repository = new EntityRepository;
        $repository->save($data);

        redirect('manage/entities');
        exit;
    }

    public function delete() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('manage/entities');
            exit;
        }
        
        $this->requireAuth();

        $id = (int) $_POST["id"];
        $repository = new EntityRepository;
        $repository->delete($id, [
            'user_id' => Session::get('user_id')
        ]);

        redirect('manage/entities');
        exit;
    }    

    protected function normalizeData(array $data) {
        return [
            "id"      => empty($data["id"]) ? null : (int) $data["id"],
            "user_id" => Session::get('user_id'),
            "name"    => trim($data["name"] ?? ""),
            "type_id" => empty($data["type_id"]) ? null : (int) $data["type_id"]
        ];
    }    
}
