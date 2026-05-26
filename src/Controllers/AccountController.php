<?php 

namespace App\Controllers;

class AccountController extends Controller {
    public function index() {
        $this->view('account.twig');
    }
}