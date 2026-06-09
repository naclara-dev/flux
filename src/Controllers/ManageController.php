<?php 

namespace App\Controllers;

use App\Models\Repositories\CategoryRepository;
use App\Models\Repositories\EntityRepository;
use App\Models\Repositories\RuleRepository;
use App\Models\Repositories\WalletRepository;
use App\Models\Repositories\WalletTypeRepository;

class ManageController extends Controller {
    public function index() {     
        $this->view('manage/index.twig', [
            'categories_count' => (new CategoryRepository)->countFromUser(),
            'wallets_count'    => (new WalletRepository)->countFromUser(),
            'entities_count'   => (new EntityRepository)->countFromUser(),
            'rules_count'      => (new RuleRepository)->countFromUser()
        ]);
    }

    public function categories() {
        $repository = new CategoryRepository;
        $categories = $repository->allFromUser();

        $this->view('manage/categories.twig', [
            'categories' => $categories
        ]);
    }

    public function wallets() {
        $repository = new WalletRepository;
        $wallets = $repository->allFromUser();
        $walletTypes = (new WalletTypeRepository)->all();

        $this->view('manage/wallets.twig', [
            'wallets' => $wallets,
            'wallet_types' => $walletTypes
        ]);
    }

    public function entities() {
        $this->view('manage/entities.twig');
    }

    public function rules() {
        $this->view('manage/rules.twig');
    }
}
