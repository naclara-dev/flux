<?php 

namespace App\Controllers;

use App\Models\Repositories\UserRepository;

class AccountController extends Controller {
    public function index() {
        $this->view('account.twig');
    }

    public function store() {
        $data = [
            'name' => $_POST['name'] ?? null,
            'email' => $_POST['email'] ?? null,
            'password' => password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT),
            'google_id' => null,
            'auth_provider' => 'local',
        ];

        $repository = new UserRepository;
        $repository->save($data);
    }

    public function checkEmail() {
        $email = trim($_POST['email'] ?? '');
        $repository = new UserRepository;
        $user = $email !== '' ? $repository->find([
            'email' => $email
        ]) : null;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'available' => empty($user),
        ]);
    }
}
