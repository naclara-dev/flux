<?php 

namespace App\Controllers;

use App\Core\Session;
use App\Models\Repositories\UserRepository;
use App\Presenters\DashboardPresenter;
use App\Services\DashboardService;
use App\Services\PeriodicTransactionService;

class HomeController extends Controller {
    public function index() {  
        $this->requireAuth();

        // Define o usuario autenticado e carrega o feedback temporario das previsoes
        $user_id = (int) Session::get('user_id');
        $periodicFeedback = Session::get('periodic_feedback');

        // Remove o feedback apos disponibiliza-lo para a proxima renderizacao
        Session::remove('periodic_feedback');

        // Carrega os dados consolidados da dashboard
        $dashboard = (new DashboardService)->build();
        $dashboardView = (new DashboardPresenter($dashboard))->present();

        $this->view('home.twig', [
            'dashboard' => $dashboardView,
            'user' => (new UserRepository)->find(["id" => $user_id]),
            'periodic_feedback' => $periodicFeedback
        ]);
    }

    public function generatePeriodicTransactions() {
        // Verifica se a geracao foi solicitada por POST
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            // Interrompe requisicoes feitas por outros metodos
            redirect();
            exit;
        }

        // Verifica se o usuario possui uma sessao autenticada
        $this->requireAuth();

        // Define o usuario autenticado e o ciclo financeiro atual
        $userId = (int) Session::get('user_id');
        $dashboardService = new DashboardService;
        $currentCycle = $dashboardService->getCurrentCycle();

        // Cria as transacoes periodicas previstas para o ciclo atual
        $created = (new PeriodicTransactionService)->generateForCycle(
            $userId,
            $currentCycle->start,
            $currentCycle->end
        );

        // Define a mensagem exibida apos o redirecionamento
        $message = 'Nenhuma previsão pendente foi encontrada para o ciclo atual.';

        // Verifica se apenas uma previsao foi criada
        if ($created === 1) {
            // Define o texto singular para o feedback
            $message = '1 previsão foi inserida no ciclo atual.';
        }

        // Verifica se mais de uma previsao foi criada
        if ($created > 1) {
            // Define o texto plural para o feedback
            $message = "$created previsões foram inseridas no ciclo atual.";
        }

        // Salva o feedback exibido apos o redirecionamento
        Session::set('periodic_feedback', [
            'type' => 'success',
            'message' => $message
        ]);

        redirect();
        exit;
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

    /**
     * Exibe todas as transações de um ciclo em uma página preparada para impressão.
     */
    public function printCycle() {
        // Verifica se o usuário possui uma sessão autenticada
        $this->requireAuth();

        // Define a data usada para localizar o ciclo solicitado
        $referenceDate = $_GET['date'] ?? '';

        // Verifica se a data recebida possui o formato esperado
        if (!$this->isValidDate($referenceDate)) {
            // Interrompe a impressão quando a referência é inválida
            http_response_code(422);
            echo 'Data de referência inválida.';
            return;
        }

        // Carrega e prepara o ciclo para a view de impressão
        $cycle = (new DashboardService)->getCycleAt($referenceDate);
        $cycleView = (new DashboardPresenter)->presentCycle($cycle);

        // Exibe a página independente usada pelo navegador para gerar o PDF
        $this->view('dashboard/cycle-print.twig', [
            'cycle' => $cycleView
        ]);
    }

    private function isValidDate(string $date): bool {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        return $parsed && $parsed->format('Y-m-d') === $date;
    }
}
