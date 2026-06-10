<?php

namespace App\Services;

use App\Core\Session;
use App\DTOs\Dashboard\Balance;
use App\DTOs\Dashboard\Cycle;
use App\DTOs\Dashboard\Dashboard;
use App\DTOs\Dashboard\Milestone;
use App\DTOs\Dashboard\NextIncome;
use App\Models\Repositories\TransactionRepository;
use App\Models\Repositories\WalletRepository;

class DashboardService {
    private $userID;
    private $transactions;
    private $wallets;

    public function __construct() {
        $this->userID = (int) Session::get('user_id');
        $this->transactions = new TransactionRepository;
        $this->wallets = new WalletRepository;
    }

    /**
     * Monta o DTO principal da dashboard, agrupando cada area da tela.
     * @return \App\DTOs\Dashboard\Dashboard
     */
    public function build(): Dashboard {
        $balance = $this->getBalance();
        $currentCycle = $this->getCurrentCycle();
        $nextCycle = $this->getNextCycle();
        $nextIncome = $this->getNextIncome();
        $milestones = $this->getMilestones();

        return new Dashboard(
            $balance,
            $currentCycle,
            $nextCycle,
            $nextIncome,
            $milestones
        );
    }

    /**
     * Obtem um resumo do estado atual do fluxo de caixa.
     * @return \App\DTOs\Dashboard\Balance
     */
    public function getBalance(): Balance {
        $currentCycle = $this->getCurrentCycle();

        // Saldo atual: saldo inicial da wallet + total de transacoes efetuadas.
        $current = $this->getCurrentBalance();

        // Comprometido: todas as transacoes pendentes previstas para o ciclo atual.
        $commited = $this->transactions->sumCommittedUntil(
            $this->userID,
            $this->getTodayDate(),
            $currentCycle->end
        );

        // Disponivel: saldo atual - valor comprometido.
        $spendable = max(0, $current - $commited);

        return new Balance($current, $commited, $spendable);
    }

    /**
     * Obtem o ciclo financeiro em andamento.
     * @return \App\DTOs\Dashboard\Cycle
     */
    public function getCurrentCycle(): Cycle {
        // Data Inicial do Ciclo: data da ultima entrada registrada.
        $start = $this->getCurrentCycleStartDate();

        // Data Final do Ciclo: data da proxima entrada registrada.
        $end = $this->getCurrentCycleEndDate();

        // Se a data final for menor ou igual a data inicial, forca o fim para o dia seguinte, evitando ciclos com 0 dias.
        if ($end <= $start) {
            $end = $this->getToday()->modify('+1 day')->format('Y-m-d');
        }

        // Progresso: andamento do ciclo em relacao ao dia atual.
        $progress = $this->getCycleProgress($start, $end, $this->getTodayDate());

        return $this->buildCycle($start, $end, $progress, $this->getCurrentBalance());
    }

    /**
     * Obtem o proximo ciclo financeiro projetado.
     * @return \App\DTOs\Dashboard\Cycle
     */
    public function getNextCycle(): Cycle {
        // Obtem o ciclo em andamento.
        $currentCycle = $this->getCurrentCycle();

        // Data Inicial: data final do ciclo atual.
        $start = $currentCycle->end;

        // Obtem a primeira entrada apos a data inicial.
        $nextIncome = $this->transactions->findNextIncomeAfter($this->userID, $start);

        // Data Final: proxima entrada apos a data inicial.
        $end = $nextIncome['occurrence_date'] ?? (new \DateTimeImmutable($start))->modify('+30 days')->format('Y-m-d');

        // Se a data final for menor ou igual a data inicial, forca o fim para o dia seguinte, evitando ciclos com 0 dias.
        if ($end <= $start) {
            $end = (new \DateTimeImmutable($start))->modify('+1 day')->format('Y-m-d');
        }

        // Saldo projetado no inicio do proximo ciclo.
        $openingBalance = $this->getCurrentBalance()
            + $this->transactions->sumAmountInCycle($this->userID, $this->getTodayDate(), $start);

        // Progresso: sempre zerado, visto que o ciclo ainda nao comecou.
        return $this->buildCycle($start, $end, 0, $openingBalance);
    }

    /**
     * Obtem a proxima entrada prevista.
     * @return \App\DTOs\Dashboard\NextIncome
     */
    public function getNextIncome(): NextIncome {
        // Busca a proxima entrada a partir da data de hoje.
        $nextIncome = $this->transactions->findNextIncome($this->userID, $this->getTodayDate());

        return new NextIncome(
            $nextIncome['occurrence_date'] ?? null,
            (float) ($nextIncome['amount'] ?? 0)
        );
    }

    /**
     * Obtem os proximos marcos do fluxo.
     * @return array
     */
    public function getMilestones(): array {
        // Obtem o proximo ciclo.
        $nextCycle = $this->getNextCycle();

        // Obtem as transacoes registradas a partir de hoje ate o fim do proximo ciclo.
        $transactions = $this->transactions->allInCycleFromUser(
            $this->userID,
            $this->getTodayDate(),
            $nextCycle->end
        );

        $milestones = [];
        $limit = 3;

        // Limita os marcos a quantidade definida para o bloco lateral da home.
        foreach (array_slice($transactions, 0, $limit) as $transaction) {
            $milestones[] = new Milestone(
                $transaction['title'],
                $transaction['occurrence_date'],
                (float) $transaction['amount']
            );
        }

        return $milestones;
    }

    /**
     * Monta um ciclo com totais e transacoes cruas.
     * @return \App\DTOs\Dashboard\Cycle
     */
    private function buildCycle(string $start, string $end, int $progress, float $openingBalance): Cycle {
        // Entradas: todas as entradas registradas no ciclo.
        $income = $this->transactions->sumIncomeInCycle($this->userID, $start, $end);

        // Gastos: todas as saidas registradas no ciclo.
        $expenses = $this->transactions->sumExpenseInCycle($this->userID, $start, $end);

        // Saldo: entradas - gastos.
        $balance = $income - $expenses;

        // Transacoes cruas do ciclo. A preparacao visual fica no presenter.
        $cycleTransactions = $this->transactions->allInCycleFromUser($this->userID, $start, $end);

        return new Cycle(
            $start,
            $end,
            $income,
            $expenses,
            $balance,
            $progress,
            $cycleTransactions,
            $openingBalance
        );
    }

    /**
     * Obtem o saldo atual.
     * @return float
     */
    private function getCurrentBalance(): float {
        // Saldo Inicial: soma do saldo inicial de todas as wallets.
        $initialBalance = $this->wallets->sumInitialBalanceFromUser($this->userID);

        // Transacoes Pagas: todas as transacoes registradas como pagas.
        $paidTransactions = $this->transactions->sumPaidAmountFromUser($this->userID);

        return $initialBalance + $paidTransactions;
    }

    /**
     * Obtem a data em que o ciclo atual comeca.
     * @return string
     */
    private function getCurrentCycleStartDate(): string {
        // Busca a data da ultima entrada registrada.
        // Se nao houver, seta a data de hoje como inicio do ciclo.
        return $this->transactions->findPreviousIncomeDate(
            $this->userID,
            $this->getTodayDate()
        ) ?? $this->getTodayDate();
    }

    /**
     * Obtem a data em que o ciclo atual termina.
     * @return string
     */
    private function getCurrentCycleEndDate(): string {
        // Busca a data da proxima entrada prevista.
        $nextIncome = $this->transactions->findNextIncome(
            $this->userID,
            $this->getTodayDate()
        );

        // Se nao houver, seta a data de hoje + 30 dias como o fim do ciclo.
        return $nextIncome['occurrence_date'] ?? $this->getToday()->modify('+30 days')->format('Y-m-d');
    }

    /**
     * Obtem o percentual de andamento do ciclo.
     * @return int
     */
    private function getCycleProgress(string $start, string $end, string $current): int {
        // Data Inicial.
        $startDate = new \DateTimeImmutable($start);

        // Data Final.
        $endDate = new \DateTimeImmutable($end);

        // Data Atual.
        $currentDate = new \DateTimeImmutable($current);

        // Total de Dias: diferenca de dias entre o comeco e o fim do ciclo.
        $totalDays = max(1, (int) $startDate->diff($endDate)->format('%a'));

        // Dias Decorridos: diferenca de dias entre o comeco do ciclo e o dia atual.
        $elapsedDays = (int) $startDate->diff($currentDate)->format('%r%a');

        // Progresso: razao entre os dias ja decorridos e o total de dias do ciclo.
        $progress = (int) round(($elapsedDays / $totalDays) * 100);

        return max(0, min($progress, 100));
    }

    /**
     * Obtem a data de hoje.
     * @return \DateTimeImmutable
     */
    private function getToday(): \DateTimeImmutable {
        return new \DateTimeImmutable('today');
    }

    /**
     * Obtem a data de hoje em formato yyyy-mm-dd.
     * @return string
     */
    private function getTodayDate(): string {
        return $this->getToday()->format('Y-m-d');
    }
}
