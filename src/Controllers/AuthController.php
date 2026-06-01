<?php

namespace App\Controllers;

class AuthController extends Controller {
    public function index() {
        $this->view('auth/login.twig');
    }
}