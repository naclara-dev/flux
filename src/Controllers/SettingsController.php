<?php 

namespace App\Controllers;

class SettingsController extends Controller {
    public function index() {
        $this->view('settings.twig');
    }
}