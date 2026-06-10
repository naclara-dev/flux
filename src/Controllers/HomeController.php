<?php 

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\UserRepository;
use App\Presenters\DashboardPresenter;
use App\Services\DashboardService;

class HomeController extends Controller {
    public function index() {  
        $this->requireAuth();

        $user_id = (int) Session::get('user_id');
        $dashboard = (new DashboardService)->build();
        $dashboardView = (new DashboardPresenter($dashboard))->present();

        $this->view('home.twig', [
            'dashboard' => $dashboardView,
            'user' => (new UserRepository)->find(["id" => $user_id])
        ]);
    }
}
