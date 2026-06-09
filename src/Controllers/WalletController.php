<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\WalletRepository;

class WalletController extends Controller {
    public function store() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('manage/wallets');
            exit;
        }

        $this->requireAuth();

        $data = $this->normalizeData($_POST);
        $repository = new WalletRepository;
        $repository->save($data);

        redirect('manage/wallets');
        exit;
    }

    public function delete() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('manage/wallets');
            exit;
        }
        
        $this->requireAuth();

        $id = (int) $_POST["id"];
        $repository = new WalletRepository;
        $repository->delete($id, [
            'user_id' => Session::get('user_id')
        ]);

        redirect('manage/wallets');
        exit;
    }

    protected function normalizeData(array $data) {
        return [
            'id' => empty($data['id']) ? null : (int) $data['id'],
            'user_id' => Session::get('user_id'),
            'name' => trim($data['name'] ?? ''),
            'type_id' => empty($data['type_id']) ? null : (int) $data['type_id'],
            'initial_balance' => moneyToFloat($data['initial_balance'] ?? '0'),
            'active' => !empty($data['active']) ? 1 : 0,
        ];
    }
}
