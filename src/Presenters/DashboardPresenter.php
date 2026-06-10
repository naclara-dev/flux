<?php

namespace App\Presenters;

use App\DTOs\Dashboard\Cycle;
use App\DTOs\Dashboard\Dashboard;

class DashboardPresenter {
    private $dashboard;

    public function __construct(Dashboard $dashboard) {
        $this->dashboard = $dashboard;
    }

    public function present(): array {
        return [
            'balance' => $this->presentBalance(),
            'currentCycle' => $this->presentCycle($this->dashboard->currentCycle, false),
            'nextCycle' => $this->presentCycle($this->dashboard->nextCycle, true),
            'nextIncome' => $this->presentNextIncome(),
            'milestones' => $this->presentMilestones(),
        ];
    }

    private function presentBalance(): array {
        return [
            'current' => $this->dashboard->balance->current,
            'commited' => $this->dashboard->balance->commited,
            'spendable' => $this->dashboard->balance->spendable,
            'currentLabel' => $this->formatMoney($this->dashboard->balance->current),
            'commitedLabel' => $this->formatMoney($this->dashboard->balance->commited),
            'spendableLabel' => $this->formatMoney($this->dashboard->balance->spendable),
        ];
    }

    private function presentCycle(Cycle $cycle, bool $withHighlights): array {
        $transactions = $this->presentTransactions($cycle->transactions);

        return [
            'start' => $cycle->start,
            'end' => $cycle->end,
            'income' => $cycle->income,
            'expenses' => $cycle->expenses,
            'balance' => $cycle->balance,
            'progress' => $cycle->progress,
            'label' => $this->formatCycleLabel($cycle->start, $cycle->end),
            'startDayLabel' => (new \DateTimeImmutable($cycle->start))->format('d'),
            'incomeLabel' => '+' . $this->formatMoney($cycle->income),
            'expensesLabel' => '-' . $this->formatMoney($cycle->expenses),
            'balanceLabel' => $this->formatMoney($cycle->balance),
            'transactions' => $transactions,
            'largestExpense' => $withHighlights ? $this->getLargestExpenseHighlight($transactions) : [],
            'attention' => $withHighlights ? $this->getAttentionHighlight($transactions, $cycle->openingBalance) : [],
        ];
    }

    private function presentNextIncome(): array {
        return [
            'date' => $this->dashboard->nextIncome->date,
            'amount' => $this->dashboard->nextIncome->amount,
            'dateLabel' => $this->formatShortDate($this->dashboard->nextIncome->date),
            'amountLabel' => $this->formatMoneyWithExplicitSignal($this->dashboard->nextIncome->amount),
        ];
    }

    private function presentMilestones(): array {
        return array_map(function ($milestone) {
            return [
                'title' => $milestone->title,
                'date' => $milestone->date,
                'amount' => $milestone->amount,
                'dateLabel' => $this->formatShortDate($milestone->date),
                'amountLabel' => $this->formatMoneyWithExplicitSignal($milestone->amount),
            ];
        }, $this->dashboard->milestones);
    }

    private function presentTransactions(array $transactions): array {
        return array_map(function ($transaction) {
            $amount = (float) $transaction['amount'];
            $categoryName = $transaction['category_name'] ?: 'sem categoria';
            $walletName = $transaction['wallet_name'] ?: 'sem wallet';
            $entityName = $transaction['entity_name'] ?: '';
            $templateName = $transaction['template_title'] ?: 'sem template';
            $paymentMethodName = $transaction['payment_method_name'] ?: 'escolha uma forma';

            $transaction['amount_label'] = $this->formatMoneyWithExplicitSignal($amount);
            $transaction['form_amount_label'] = number_format($amount, 2, ',', '.');
            $transaction['date_label'] = $this->formatShortDate($transaction['occurrence_date']);
            $transaction['status_label'] = !empty($transaction['paid']) ? 'pago' : 'pendente';
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
            $transaction['meta_label'] = $entityName
                ? $categoryName . ' - ' . $entityName . ' - ' . $walletName
                : $categoryName . ' - ' . $walletName;

            return $transaction;
        }, $transactions);
    }

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
                'description' => 'nenhuma saida encontrada neste ciclo.',
            ];
        }

        $status = !empty($largestExpense['paid']) ? 'pagos' : 'previstos';

        return [
            'title' => $largestExpense['title'],
            'description' => $this->formatMoney(abs((float) $largestExpense['amount'])) . ' ' . $status . ' para ' . $largestExpense['date_label'] . '.',
        ];
    }

    private function getAttentionHighlight(array $transactions, float $openingBalance): array {
        if (empty($transactions)) {
            return [
                'title' => 'sem ponto de atencao',
                'description' => 'nao ha lancamentos suficientes para projetar aperto neste ciclo.',
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
            'description' => 'evite gastos acima de ' . $this->formatMoney($lowestBalance) . ' antes desse marco.',
        ];
    }

    private function formatMoney(float $value): string {
        $signal = $value < 0 ? '-' : '';

        return $signal . 'R$ ' . number_format(abs($value), 2, ',', '.');
    }

    private function formatMoneyWithExplicitSignal(float $value): string {
        $signal = $value < 0 ? '-' : '+';

        return $signal . 'R$ ' . number_format(abs($value), 2, ',', '.');
    }

    private function formatCycleLabel(string $start, string $end): string {
        return $this->formatShortDate($start) . ' - ' . $this->formatShortDate($end);
    }

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
