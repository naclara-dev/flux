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

    public function cycle() {
        $this->requireAuth();

        $referenceDate = $_GET['date'] ?? '';

        if (!$this->isValidDate($referenceDate)) {
            http_response_code(422);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'error' => 'Data de referencia invalida.'
            ]);
            return;
        }

        $service = new DashboardService;
        $cycle = $service->getCycleAt($referenceDate);
        $presenter = new DashboardPresenter;
        $cycleView = $presenter->presentCycle($cycle);

        $today = new \DateTimeImmutable('today');
        $start = new \DateTimeImmutable($cycle->start);
        $end = new \DateTimeImmutable($cycle->end);

        if ($today < $start) {
            $cycleView['description'] = 'ciclo futuro projetado';
        } elseif ($today >= $end) {
            $cycleView['description'] = 'ciclo encerrado';
        } else {
            $cycleView['description'] = 'ciclo atual ate o proximo recebimento';
        }

        $cycleView['previousReference'] = $start->modify('-1 day')->format('Y-m-d');
        $cycleView['nextReference'] = $end->format('Y-m-d');

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($cycleView);
    }

    private function isValidDate(string $date): bool {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
