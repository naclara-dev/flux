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
        $user = $repository->find([
            'email' => $data['email']
        ]);

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

    public function checkEmail() {
        $email = trim($_POST['email'] ?? '');
        $repository = new UserRepository;
        $user = $email !== '' ? $repository->find([
            'email' => $email
        ]) : null;

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'exists' => !empty($user),
        ]);
    }

    public function logoff() {
        Session::remove('user_id');
        redirect();
        exit;
    }
}
