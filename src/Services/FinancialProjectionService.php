<?php

namespace App\Services;

use App\Models\Repositories\TransactionRepository;
use App\Models\Repositories\WalletRepository;

class FinancialProjectionService
{
    private $walletRepository;
    private $transactionRepository;

    public function __construct()
    {
        $this->walletRepository = new WalletRepository;
        $this->transactionRepository = new TransactionRepository;
    }

    public function getHomeSummary(int $userId): array
    {
        $today = new \DateTimeImmutable('today');
        $todayDate = $today->format('Y-m-d');
        $nextIncome = $this->transactionRepository->findNextIncome($userId, $todayDate);
        $cycleStartDate = $this->transactionRepository->findPreviousIncomeDate($userId, $todayDate) ?? $todayDate;
        $cycleEndDate = $nextIncome['occurrence_date'] ?? $today->modify('+30 days')->format('Y-m-d');

        if ($cycleEndDate <= $cycleStartDate) {
            $cycleEndDate = $today->modify('+1 day')->format('Y-m-d');
        }

        $currentBalance = $this->walletRepository->sumInitialBalanceFromUser($userId)
            + $this->transactionRepository->sumPaidAmountFromUser($userId);

        $cycleBalance = $this->transactionRepository->sumAmountInCycle($userId, $cycleStartDate, $cycleEndDate);
        $committed = $this->transactionRepository->sumCommittedUntil($userId, $todayDate, $cycleEndDate);

        return [
            'current_balance' => $currentBalance,
            'cycle_balance' => $cycleBalance,
            'next_income' => $nextIncome,
            'committed' => $committed,
            'cycle_start_date' => $cycleStartDate,
            'cycle_end_date' => $cycleEndDate,
            'current_balance_label' => $this->formatMoney($currentBalance),
            'cycle_balance_label' => $this->formatMoney($cycleBalance),
            'next_income_label' => $this->formatShortDate($nextIncome['occurrence_date'] ?? null),
            'committed_label' => $this->formatMoney($committed),
        ];
    }

    private function formatMoney(float $value): string
    {
        $signal = $value < 0 ? '-' : '';

        return $signal . 'R$ ' . number_format(abs($value), 2, ',', '.');
    }

    private function formatShortDate(?string $date): string
    {
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
