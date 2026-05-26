<?php 

namespace App\Controllers;

class ManageController extends Controller {
    public function index() {
        $this->view('organization/index.twig');
    }
}