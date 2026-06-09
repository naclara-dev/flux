<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\TemplateRepository;
use App\Models\Repositories\TransactionRepository;

class TransactionController extends Controller {
    public function store() {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            redirect();
            exit;
        }

        $this->requireAuth();

        $data = $this->normalizeData($_POST);
        $repository = new TransactionRepository;
        $repository->save($data);

        redirect();
        exit;
    }

    protected function normalizeData(array $data) {
        $paid = !empty($data['paid']);
        $templateId = empty($data['template_id']) ? null : (int) $data['template_id'];
        $title = trim($data['title'] ?? '');

        if ($title === '' && $templateId) {
            $template = (new TemplateRepository)->find([
                'id' => $templateId,
                'user_id' => Session::get('user_id')
            ]);

            $title = trim($template['title'] ?? '');
        }

        return [
            'id' => empty($data['id']) ? null : (int) $data['id'],
            'user_id' => Session::get('user_id'),
            'wallet_id' => empty($data['wallet_id']) ? null : (int) $data['wallet_id'],
            'category_id' => empty($data['category_id']) ? null : (int) $data['category_id'],
            'entity_id' => empty($data['entity_id']) ? null : (int) $data['entity_id'],
            'template_id' => $templateId,
            'payment_method_id' => empty($data['payment_method_id']) ? null : (int) $data['payment_method_id'],
            'title' => $title,
            'paid' => $paid ? 1 : 0,
            'amount' => moneyToFloat($data['amount'] ?? '0'),
            'occurrence_date' => $data['occurrence_date'] ?? null,
            'due_date' => empty($data['due_date']) ? null : $data['due_date'],
            'paid_at' => !$paid || empty($data['paid_at']) ? null : $data['paid_at'],
        ];
    }
}
