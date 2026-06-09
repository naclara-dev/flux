<?php 

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\UserRepository;
use App\Services\FinancialProjectionService;

class HomeController extends Controller {
    public function index() {  
        $this->requireAuth();

        $user_id = (int) Session::get('user_id');
        $summary = (new FinancialProjectionService)->getHomeSummary($user_id);

        $this->view('home.twig', [
            'summary' => $summary,
            'user' => (new UserRepository)->find(["id" => $user_id])
        ]);
    }
}
