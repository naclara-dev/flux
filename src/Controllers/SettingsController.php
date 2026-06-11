<?php 

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\EntityRepository;
use App\Models\Repositories\PaymentMethodRepository;
use App\Models\Repositories\SettingRepository;
use App\Models\Repositories\WalletRepository;

class SettingsController extends Controller {
    public function index() {
        $this->requireAuth();

        $userId = (int) Session::get('user_id');

        $this->view('settings.twig', [
            'settings' => (new SettingRepository)->firstFromUser($userId),
            'payment_methods' => (new PaymentMethodRepository)->all(),
            'wallets' => (new WalletRepository)->allFromUser(),
            'entities' => (new EntityRepository)->allFromUser(),
        ]);
    }

    public function store() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect('settings');
            exit;
        }

        $this->requireAuth();

        $data = $this->normalizeData($_POST);
        $repository = new SettingRepository;
        $repository->save($data);

        redirect('settings');
        exit;
    }

    protected function normalizeData(array $data) {
        return [
            'id' => empty($data['id']) ? null : (int) $data['id'],
            'user_id' => Session::get('user_id'),
            'default_payment_method_id' => empty($data['default_payment_method_id']) ? null : (int) $data['default_payment_method_id'],
            'default_wallet_id' => empty($data['default_wallet_id']) ? null : (int) $data['default_wallet_id'],
            'default_entity_id' => empty($data['default_entity_id']) ? null : (int) $data['default_entity_id'],
        ];
    }
}
