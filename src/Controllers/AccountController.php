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
        $userId = $repository->save($data);

        if ($userId) {
            Session::set('user_id', $userId);
        }

        redirect();
        exit;
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
            // Interrompe a atualização quando o usuário não foi encontrado
            $this->redirectWithFeedback('não foi possível localizar sua conta.');
        }

        $email = trim($_POST['email'] ?? '');
        $existingUser = $repository->find([
            'email' => $email
        ]);

        if (!empty($existingUser) && (int) $existingUser['id'] !== (int) $user['id']) {
            // Interrompe a atualização quando o e-mail pertence a outra conta
            $this->redirectWithFeedback('este e-mail já está cadastrado.');
        }

        $password = $user['password'];

        // Verifica se o usuário solicitou a alteração da senha
        if (!empty($_POST['change_password'])) {
            // Define as senhas informadas no formulário
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['password'] ?? '';
            $passwordConfirmation = $_POST['password_confirmation'] ?? '';

            // Verifica se a senha atual corresponde à senha cadastrada
            if ($currentPassword === '' || !password_verify($currentPassword, $user['password'])) {
                // Interrompe a alteração e mantém os campos de senha visíveis
                $this->redirectWithFeedback('a senha atual não confere.', true);
            }

            // Verifica se a nova senha foi confirmada corretamente
            if ($newPassword === '' || $newPassword !== $passwordConfirmation) {
                // Interrompe a alteração e mantém os campos de senha visíveis
                $this->redirectWithFeedback('a confirmação da nova senha não confere.', true);
            }

            // Define o hash da nova senha
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

        // Salva a confirmação para exibição após o redirecionamento
        Session::set('account_feedback', [
            'type' => 'success',
            'message' => 'seus dados foram atualizados com sucesso.',
            'change_password' => false,
        ]);

        redirect('settings');
        exit;
    }

    /**
     * Salva uma mensagem temporária e retorna para as configurações.
     */
    private function redirectWithFeedback(string $message, bool $changePassword = false) {
        // Salva o feedback consumido pela tela de configurações
        Session::set('account_feedback', [
            'type' => 'error',
            'message' => $message,
            'change_password' => $changePassword,
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
