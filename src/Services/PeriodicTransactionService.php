<?php

namespace App\Services;

use App\Models\Repositories\TemplateRepository;
use App\Models\Repositories\TransactionRepository;

class PeriodicTransactionService {
    private $templates;
    private $transactions;

    public function __construct() {
        // Inicializa o repositorio de templates periodicos
        $this->templates = new TemplateRepository;

        // Inicializa o repositorio de transacoes geradas
        $this->transactions = new TransactionRepository;
    }

    public function generateForCycle(int $userId, string $startDate, string $endDate): int {
        // Carrega os templates ativos com execucao dentro do ciclo informado
        $templates = $this->templates->allDueInCycleFromUser($userId, $startDate, $endDate);
        $created = 0;

        // Percorre todos os templates pendentes do ciclo
        foreach ($templates as $template) {
            // Define a data que deve gerar uma transacao
            $runDate = $template['next_run_date'] ?? null;

            // Verifica se a data de execucao esta disponivel
            if (empty($runDate)) {
                // Interrompe o template sem data de execucao
                continue;
            }

            // Verifica se a transacao do template ja existe na data prevista
            if (!$this->transactions->existsFromTemplateOnDate($userId, (int) $template['id'], $runDate)) {
                // Salva a transacao prevista a partir dos dados do template
                $this->transactions->save($this->transactionDataFromTemplate($template, $userId, $runDate));
                $created++;
            }

            // Define a proxima data de execucao do template
            $nextRunDate = $this->getNextRunDate($template, $runDate);

            // Salva a proxima execucao do template processado
            $this->templates->updateNextRunDate((int) $template['id'], $userId, $nextRunDate);
        }

        return $created;
    }

    private function transactionDataFromTemplate(array $template, int $userId, string $runDate): array {
        return [
            'id' => null,
            'user_id' => $userId,
            'wallet_id' => empty($template['wallet_id']) ? null : (int) $template['wallet_id'],
            'type' => $template['type'] ?? null,
            'category_id' => empty($template['category_id']) ? null : (int) $template['category_id'],
            'entity_id' => empty($template['entity_id']) ? null : (int) $template['entity_id'],
            'template_id' => (int) $template['id'],
            'payment_method_id' => null,
            'title' => trim($template['title'] ?? ''),
            'paid' => 0,
            'defines_cycle' => !empty($template['defines_cycle']) ? 1 : 0,
            'amount' => (float) ($template['amount'] ?? 0),
            'occurrence_date' => $runDate,
            'due_date' => $runDate,
            'paid_at' => null,
        ];
    }

    private function getNextRunDate(array $template, string $runDate): string {
        // Define os dados de periodicidade do template
        $frequencyUnit = $template['frequency_unit'] ?? 'MONTH';
        $intervalValue = max(1, (int) ($template['interval_value'] ?? 1));
        $monthDay = max(1, min(31, (int) ($template['month_day'] ?? 1)));
        $date = new \DateTimeImmutable($runDate);

        // Verifica se a frequencia deve avancar em dias
        if ($frequencyUnit === 'DAY') {
            return $date->modify("+$intervalValue days")->format('Y-m-d');
        }

        // Verifica se a frequencia deve avancar em semanas
        if ($frequencyUnit === 'WEEK') {
            return $date->modify("+$intervalValue weeks")->format('Y-m-d');
        }

        // Verifica se a frequencia deve avancar em anos
        if ($frequencyUnit === 'YEAR') {
            return $this->dateInMonth(
                ((int) $date->format('Y')) + $intervalValue,
                (int) $date->format('m'),
                $monthDay
            );
        }

        return $this->nextMonthDate($date, $intervalValue, $monthDay);
    }

    private function nextMonthDate(\DateTimeImmutable $date, int $intervalValue, int $monthDay): string {
        // Calcula o mes de referencia sem carregar o dia atual para meses menores
        $target = $date->modify('first day of this month')->modify("+$intervalValue months");

        return $this->dateInMonth((int) $target->format('Y'), (int) $target->format('m'), $monthDay);
    }

    private function dateInMonth(int $year, int $month, int $day): string {
        // Inicializa a data no primeiro dia do mes de destino
        $baseDate = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));

        // Calcula o ultimo dia valido para o mes de destino
        $safeDay = min($day, (int) $baseDate->format('t'));

        return $baseDate->setDate($year, $month, $safeDay)->format('Y-m-d');
    }
}
