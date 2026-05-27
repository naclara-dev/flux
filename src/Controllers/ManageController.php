<?php 

namespace App\Controllers;

use App\Models\Repositories\CategoryRepository;

class ManageController extends Controller {
    public function index() {
        $this->view('manage/index.twig');
    }

    public function categories() {
        $repository = new CategoryRepository;
        $categories = $repository->listAll();

        $this->view('manage/categories.twig', [
            'categories' => $categories
        ]);
    }
    
    public function wallets() {
        $this->view('manage/wallets.twig');
    }

    public function entities() {
        $this->view('manage/entities.twig');
    }

    public function rules() {
        $this->view('manage/rules.twig');
    }
}