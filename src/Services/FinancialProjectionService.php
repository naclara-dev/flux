<?php

namespace App\Services;

use App\Models\Repositories\TransactionRepository;
use App\Models\Repositories\WalletRepository;

/**
 * Calcula os dados financeiros usados na home.
 *
 * A home do Flux trabalha com ciclos financeiros, não com meses fixos.
 * Este service concentra a leitura/agregação necessária para montar o resumo do ciclo atual.
 */
class FinancialProjectionService
{
    private $walletRepository;
    private $transactionRepository;

    /**
     * Inicializa os repositories usados nos cálculos.
     */
    public function __construct() {
        $this->walletRepository = new WalletRepository;
        $this->transactionRepository = new TransactionRepository;
    }

    /**
     * Monta todos os dados necessários sobre o card do ciclo atual.
     *
     * O ciclo atual é calculado entre o último recebimento ate hoje e o
     * proximo recebimento futuro. Se não houver proximo recebimento, o service
     * usa uma janela temporária de 30 dias para a UI continuar funcionando.
     *
     * @param int $userId ID do usuário logado.
     * @return array Resumo financeiro já com valores crus e labels formatadas.
     */
    public function getHomeSummary(int $userId): array {
        $today = new \DateTimeImmutable('today');
        $todayDate = $today->format('Y-m-d');

        // Define os limites do ciclo com base nos recebimentos cadastrados.
        $nextIncome = $this->transactionRepository->findNextIncome($userId, $todayDate);
        $cycleStartDate = $this->transactionRepository->findPreviousIncomeDate($userId, $todayDate) ?? $todayDate;
        $cycleEndDate = $nextIncome['occurrence_date'] ?? $today->modify('+30 days')->format('Y-m-d');

        // Evita ciclo com duração zero ou negativa quando o recebimento cai hoje.
        if ($cycleEndDate <= $cycleStartDate) {
            $cycleEndDate = $today->modify('+1 day')->format('Y-m-d');
        }

        // Saldo atual considera apenas saldo inicial das wallets e transações pagas.
        $currentBalance = $this->walletRepository->sumInitialBalanceFromUser($userId)
            + $this->transactionRepository->sumPaidAmountFromUser($userId);

        // Saldo do ciclo considera entradas e despesas previstas ou pagas.
        $cycleIncome = $this->transactionRepository->sumIncomeInCycle($userId, $cycleStartDate, $cycleEndDate);
        $cycleExpenses = $this->transactionRepository->sumExpenseInCycle($userId, $cycleStartDate, $cycleEndDate);
        $cycleBalance = $cycleIncome - $cycleExpenses;

        // Comprometido foca nas despesas pendentes até o proximo recebimento.
        $committed = $this->transactionRepository->sumCommittedUntil($userId, $todayDate, $cycleEndDate);
        $cycleProgress = $this->getCycleProgress($cycleStartDate, $cycleEndDate, $todayDate);
        $cycleTransactions = $this->getCycleTransactions($userId, $cycleStartDate, $cycleEndDate);
        $nextCycle = $this->getNextCycleSummary($userId, $currentBalance, $todayDate, $cycleEndDate, $nextIncome);
        $spendToday = max(0, $currentBalance - $committed);
        $upcomingMilestones = array_slice(
            $this->getCycleTransactions($userId, $todayDate, $nextCycle['end_date']),
            0,
            3
        );

        return [
            'current_balance' => $currentBalance,
            'cycle_income' => $cycleIncome,
            'cycle_expenses' => $cycleExpenses,
            'cycle_balance' => $cycleBalance,
            'cycle_transactions' => $cycleTransactions,
            'next_cycle' => $nextCycle,
            'spend_today' => $spendToday,
            'upcoming_milestones' => $upcomingMilestones,
            'next_income' => $nextIncome,
            'committed' => $committed,
            'cycle_start_date' => $cycleStartDate,
            'cycle_end_date' => $cycleEndDate,
            'cycle_progress' => $cycleProgress,
            'cycle_label' => $this->formatCycleLabel($cycleStartDate, $cycleEndDate),
            'cycle_start_day_label' => (new \DateTimeImmutable($cycleStartDate))->format('d'),
            'current_balance_label' => $this->formatMoney($currentBalance),
            'cycle_income_label' => '+' . $this->formatMoney($cycleIncome),
            'cycle_expenses_label' => '-' . $this->formatMoney($cycleExpenses),
            'cycle_balance_label' => $this->formatMoney($cycleBalance),
            'spend_today_label' => $this->formatMoney($spendToday),
            'next_income_label' => $this->formatShortDate($nextIncome['occurrence_date'] ?? null),
            'committed_label' => $this->formatMoney($committed),
        ];
    }

    /**
     * Formata um valor monetário com simbolo de real.
     *
     * @param float $value Valor numérico.
     * @return string Valor formatado, exemplo: R$ 120,00 ou -R$ 120,00.
     */
    private function formatMoney(float $value): string {
        $signal = $value < 0 ? '-' : '';
        return $signal . 'R$ ' . number_format(abs($value), 2, ',', '.');
    }

    /**
     * Calcula o percentual de tempo percorrido dentro do ciclo.
     *
     * @param string $startDate Data inicial do ciclo no formato Y-m-d.
     * @param string $endDate Data final do ciclo no formato Y-m-d.
     * @param string $currentDate Data usada como referência no formato Y-m-d.
     * @return int Percentual entre 0 e 100.
     */
    private function getCycleProgress(string $startDate, string $endDate, string $currentDate): int {
        $start = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);
        $current = new \DateTimeImmutable($currentDate);

        // Garante divisor mínimo para evitar erro em ciclos de um unico dia.
        $totalDays = max(1, (int) $start->diff($end)->format('%a'));
        $elapsedDays = (int) $start->diff($current)->format('%r%a');
        $progress = (int) round(($elapsedDays / $totalDays) * 100);

        // Mantém o valor seguro para uso direto em width: X%.
        return max(0, min($progress, 100));
    }

    /**
     * Formata o período do ciclo para exibição no header.
     *
     * @param string $startDate Data inicial no formato Y-m-d.
     * @param string $endDate Data final no formato Y-m-d.
     * @return string Periodo formatado, exemplo: 20 jun - 05 jul.
     */
    private function formatCycleLabel(string $startDate, string $endDate): string {
        return $this->formatShortDate($startDate) . ' - ' . $this->formatShortDate($endDate);
    }

    /**
     * Busca e prepara as transações exibidas dentro do ciclo atual.
     *
     * Alem dos dados vindos do banco, adiciona labels e estruturas auxiliares
     * para a view renderizar os cards e preencher o modal de edição.
     *
     * @param int $userId ID do usuário logado.
     * @param string $startDate Data inicial do ciclo no formato Y-m-d.
     * @param string $endDate Data final do ciclo no formato Y-m-d.
     * @return array Lista de transacoes enriquecidas para a home.
     */
    private function getCycleTransactions(int $userId, string $startDate, string $endDate): array {
        $transactions = $this->transactionRepository->allInCycleFromUser($userId, $startDate, $endDate);

        return array_map(function ($transaction) {
            // Normaliza nomes e fallbacks para registros incompletos.
            $amount = (float) $transaction['amount'];
            $categoryName = $transaction['category_name'] ?: 'sem categoria';
            $walletName = $transaction['wallet_name'] ?: 'sem wallet';
            $entityName = $transaction['entity_name'] ?: '';
            $templateName = $transaction['template_title'] ?: 'sem template';
            $paymentMethodName = $transaction['payment_method_name'] ?: 'escolha uma forma';

            // Labels usadas diretamente nos cards da timeline.
            $transaction['amount_label'] = $this->formatMoneyWithExplicitSignal($amount);
            $transaction['form_amount_label'] = number_format($amount, 2, ',', '.');
            $transaction['date_label'] = $this->formatShortDate($transaction['occurrence_date']);
            $transaction['status_label'] = !empty($transaction['paid']) ? 'pago' : 'pendente';

            // Estruturas espelhadas em objetos da UI, facilitando acesso no Twig.
            $transaction['category'] = [
                'id' => $transaction['category_id'] ?: '',
                'name' => $categoryName,
                'color' => $transaction['category_color'] ?: '#c17fd7',
                'icon' => $transaction['category_icon'] ?: 'fa-solid fa-tag',
            ];
            $transaction['wallet'] = [
                'id' => $transaction['wallet_id'] ?: '',
                'name' => $walletName,
            ];
            $transaction['entity'] = [
                'id' => $transaction['entity_id'] ?: '',
                'name' => $entityName ?: 'escolha uma entidade',
            ];
            $transaction['template'] = [
                'id' => $transaction['template_id'] ?: '',
                'title' => $templateName,
            ];
            $transaction['payment_method'] = [
                'id' => $transaction['payment_method_id'] ?: '',
                'name' => $paymentMethodName,
            ];

            // Texto secundario compacto do card.
            $transaction['meta_label'] = $entityName
                ? $categoryName . ' - ' . $entityName . ' - ' . $walletName
                : $categoryName . ' - ' . $walletName;

            return $transaction;
        }, $transactions);
    }

    /**
     * Monta os totais e destaques do proximo ciclo financeiro.
     *
     * O proximo ciclo comeca no proximo recebimento e termina no recebimento
     * seguinte. Quando ainda nao existe um recebimento seguinte, usa 30 dias
     * como janela temporaria para manter a projecao visivel.
     *
     * @param int $userId ID do usuario logado.
     * @param float $currentBalance Saldo atual calculado para hoje.
     * @param string $todayDate Data atual no formato Y-m-d.
     * @param string $fallbackStartDate Inicio alternativo quando nao ha proximo recebimento.
     * @param array|null $nextIncome Proximo recebimento encontrado para o usuario.
     * @return array Dados do card de proximo ciclo.
     */
    private function getNextCycleSummary(int $userId, float $currentBalance, string $todayDate, string $fallbackStartDate, ?array $nextIncome): array {
        $startDate = $nextIncome['occurrence_date'] ?? $fallbackStartDate;
        $nextIncomeAfter = $this->transactionRepository->findNextIncomeAfter($userId, $startDate);
        $endDate = $nextIncomeAfter['occurrence_date'] ?? (new \DateTimeImmutable($startDate))->modify('+30 days')->format('Y-m-d');

        if ($endDate <= $startDate) {
            $endDate = (new \DateTimeImmutable($startDate))->modify('+1 day')->format('Y-m-d');
        }

        $income = $this->transactionRepository->sumIncomeInCycle($userId, $startDate, $endDate);
        $expenses = $this->transactionRepository->sumExpenseInCycle($userId, $startDate, $endDate);
        $balance = $income - $expenses;
        $transactions = $this->getCycleTransactions($userId, $startDate, $endDate);
        $openingBalance = $currentBalance + $this->transactionRepository->sumAmountInCycle($userId, $todayDate, $startDate);

        return [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_day_label' => (new \DateTimeImmutable($startDate))->format('d'),
            'label' => $this->formatCycleLabel($startDate, $endDate),
            'income' => $income,
            'expenses' => $expenses,
            'balance' => $balance,
            'income_label' => '+' . $this->formatMoney($income),
            'expenses_label' => '-' . $this->formatMoney($expenses),
            'balance_label' => $this->formatMoney($balance),
            'largest_expense' => $this->getLargestExpenseHighlight($transactions),
            'attention' => $this->getAttentionHighlight($transactions, $openingBalance),
        ];
    }

    /**
     * Encontra a maior despesa do ciclo para destacar no card.
     *
     * @param array $transactions Transacoes ja enriquecidas do ciclo.
     * @return array Dados prontos para a view.
     */
    private function getLargestExpenseHighlight(array $transactions): array {
        $largestExpense = null;

        foreach ($transactions as $transaction) {
            if ((float) $transaction['amount'] >= 0) {
                continue;
            }

            if (!$largestExpense || abs((float) $transaction['amount']) > abs((float) $largestExpense['amount'])) {
                $largestExpense = $transaction;
            }
        }

        if (!$largestExpense) {
            return [
                'title' => 'sem despesas previstas',
                'description' => 'Nenhuma saida encontrada neste ciclo.',
            ];
        }

        $status = !empty($largestExpense['paid']) ? 'pagos' : 'previstos';

        return [
            'title' => $largestExpense['title'],
            'description' => $this->formatMoney(abs((float) $largestExpense['amount'])) . ' ' . $status . ' para ' . $largestExpense['date_label'] . '.',
        ];
    }

    /**
     * Calcula o menor saldo projetado dentro do ciclo.
     *
     * A ideia e simular o saldo apos cada lancamento do proximo ciclo e
     * identificar o dia em que a folga financeira fica menor.
     *
     * @param array $transactions Transacoes ja enriquecidas do ciclo.
     * @param float $openingBalance Saldo projetado no inicio do ciclo.
     * @return array Dados prontos para a view.
     */
    private function getAttentionHighlight(array $transactions, float $openingBalance): array {
        if (empty($transactions)) {
            return [
                'title' => 'sem ponto de atencao',
                'description' => 'Nao ha lancamentos suficientes para projetar aperto neste ciclo.',
            ];
        }

        $runningBalance = $openingBalance;
        $lowestBalance = $openingBalance;
        $lowestDate = $transactions[0]['date_label'];

        foreach ($transactions as $transaction) {
            $runningBalance += (float) $transaction['amount'];

            if ($runningBalance < $lowestBalance) {
                $lowestBalance = $runningBalance;
                $lowestDate = $transaction['date_label'];
            }
        }

        if ($lowestBalance < 0) {
            return [
                'title' => 'saldo fica negativo em ' . $lowestDate,
                'description' => 'Faltam ' . $this->formatMoney(abs($lowestBalance)) . ' para cobrir os lancamentos previstos.',
            ];
        }

        return [
            'title' => 'menor folga em ' . $lowestDate,
            'description' => 'Evite gastos acima de ' . $this->formatMoney($lowestBalance) . ' antes desse marco.',
        ];
    }

    /**
     * Formata valor monetario sempre com sinal explicito.
     *
     * Usado em listas de transacoes, onde entrada e saida precisam ficar
     * visiveis de imediato.
     *
     * @param float $value Valor numerico.
     * @return string Valor formatado, exemplo: +R$ 120,00 ou -R$ 120,00.
     */
    private function formatMoneyWithExplicitSignal(float $value): string {
        $signal = $value < 0 ? '-' : '+';
        return $signal . 'R$ ' . number_format(abs($value), 2, ',', '.');
    }

    /**
     * Formata uma data curta em portugues para uso na interface.
     *
     * @param string|null $date Data no formato Y-m-d, ou null.
     * @return string Data como 05 jul, ou -- quando vazia.
     */
    private function formatShortDate(?string $date): string {
        if (empty($date)) {
            return '--';
        }

        $months = [
            '01' => 'jan',
            '02' => 'fev',
            '03' => 'mar',
            '04' => 'abr',
            '05' => 'mai',
            '06' => 'jun',
            '07' => 'jul',
            '08' => 'ago',
            '09' => 'set',
            '10' => 'out',
            '11' => 'nov',
            '12' => 'dez',
        ];

        $dateTime = new \DateTimeImmutable($date);

        return $dateTime->format('d') . ' ' . $months[$dateTime->format('m')];
    }
}
