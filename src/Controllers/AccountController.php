<?php 

namespace App\Controllers;

use App\Core\Session;
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

    public function update() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('settings');
            exit;
        }

        $this->requireAuth();

        $repository = new UserRepository;
        $user = $repository->find([
            'id' => Session::get('user_id')
        ]);

        if (empty($user)) {
            redirect('settings');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $existingUser = $repository->find([
            'email' => $email
        ]);

        if (!empty($existingUser) && (int) $existingUser['id'] !== (int) $user['id']) {
            echo 'E-mail ja cadastrado.';
            exit;
        }

        $password = $user['password'];

        if (!empty($_POST['change_password'])) {
            $newPassword = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';

            if ($newPassword === '' || $newPassword !== $passwordConfirmation) {
                echo 'A confirmacao de senha nao confere.';
                exit;
            }

            $password = password_hash($newPassword, PASSWORD_DEFAULT);
        }

        $repository->save([
            'id' => (int) $user['id'],
            'name' => trim($_POST['name'] ?? ''),
            'email' => $email,
            'password' => $password,
            'google_id' => $user['google_id'] ?? null,
            'auth_provider' => $user['auth_provider'] ?? 'local',
        ]);

        redirect('settings');
        exit;
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
