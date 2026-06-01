<?php 

namespace App\Controllers;

use App\Controllers\AuthController;

class HomeController extends Controller {
    public function index() {  
        $this->requireAuth();
        $this->view('home.twig');
    }
}