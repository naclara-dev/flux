<?php 

namespace App\Controllers;

class ManageController extends Controller {
    public function index() {
        $this->view('manage/index.twig');
    }
}