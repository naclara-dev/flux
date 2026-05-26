<?php 

namespace App\Controllers;

class ManageController extends Controller {
    public function index() {
        $this->view('manage/index.twig');
    }

    public function categories() {
        $this->view('manage/categories.twig');
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