<?php 

namespace App\Controllers;

use App\Models\Repositories\CategoryRepository;
use App\Models\Repositories\EntityRepository;
use App\Models\Repositories\EntityTypeRepository;
use App\Models\Repositories\FrequencyRepository;
use App\Models\Repositories\TemplateRepository;
use App\Models\Repositories\WalletRepository;
use App\Models\Repositories\WalletTypeRepository;

class ManageController extends Controller {
    public function index() {     
        $this->view('manage/index.twig', [
            'categories_count' => (new CategoryRepository)->countFromUser(),
            'wallets_count'    => (new WalletRepository)->countFromUser(),
            'entities_count'   => (new EntityRepository)->countFromUser(),
            'templates_count'  => (new TemplateRepository)->countFromUser()
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
        $repository = new EntityRepository;
        $entities = $repository->allFromUser();
        $entityTypes = (new EntityTypeRepository)->all();

        $this->view('manage/entities.twig', [
            'entities' => $entities,
            'entity_types' => $entityTypes
        ]);
    }

    public function templates() {
        $repository = new TemplateRepository;
        $templates = $repository->allFromUser();
        $frequencies = (new FrequencyRepository)->all();
        $wallets = (new WalletRepository)->allFromUser();
        $categories = (new CategoryRepository)->allFromUser();
        $entities = (new EntityRepository)->allFromUser();

        $this->view('manage/templates.twig', [
            'templates' => $templates,
            'frequencies' => $frequencies,
            'wallets' => $wallets,
            'categories' => $categories,
            'entities' => $entities
        ]);
    }

}
