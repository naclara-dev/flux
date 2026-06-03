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
        ];

        $repository = new UserRepository;
        $repository->save($data);
    }
}