<?php 

namespace App\Controllers;

abstract class Controller {
    protected $twig;
    
    public function __construct($twig) {
        $this->twig = $twig;
    }

    protected function view(string $view, array $data = []) {
        echo $this->twig->render($view, $data);
    }

    protected function requireAuth() {
        if (!\App\Core\Session::has('user_id')) {
            redirect('login/');
            exit;
        }
    }
}