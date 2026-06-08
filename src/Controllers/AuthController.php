<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\UserRepository;

class AuthController extends Controller {
    public function index() {
        $this->view('auth/login.twig');
    }

    public function login() {
        $data = [
            'email' => $_POST['email'] ?? null,
            'password' => $_POST['password'] ?? '',
        ]; 
        
        $repository = new UserRepository;
        $user = $repository->find($data['email'], 'email');

        if (empty($user) || $user == null) {
            echo "Usuário não encontrado";
            exit;
        }

        if (password_verify($data['password'], $user['password'])) {
            Session::set('user_id', $user['id']);
            redirect();
            exit;
        }
    }

    public function logoff() {
        Session::remove('user_id');
        redirect();
        exit;
    }
}
