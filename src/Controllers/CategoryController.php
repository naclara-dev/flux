<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\CategoryRepository;

class CategoryController extends Controller {
    public function store() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('manage/categories');
            exit;
        }

        $this->requireAuth();

        if (!Session::has('user_id')) {
            return;
        }

        $data = $this->normalizeData($_POST);
        
        $repository = new CategoryRepository;
        $repository->save($data);

        redirect('manage/categories');
        exit;
    }

    protected function normalizeData(array $data) {
        return [
            "id"      => empty($data["id"]) ? null : (int) $data["id"],
            "user_id" => Session::get('user_id'),
            "name"    => trim($data["name"] ?? ""),
            "color"   => strtolower(trim($data["color"] ?? "#c17fd7")),
            "icon"    => trim($data["icon"] ?? "")
        ];
    }
}
