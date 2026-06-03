<?php 

namespace App\Controllers;

class HomeController extends Controller {
    public function index() {  
        $isLogged = \App\Core\Session::has('user_id');

        if (!$isLogged) {
            $this->view('auth/login.twig');
            return;
        }

        $this->view('home.twig');
    }
}