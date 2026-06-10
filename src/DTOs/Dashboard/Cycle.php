<?php 

namespace App\DTOs\Dashboard;

class Cycle {
    public $start;
    public $end;
    public $income;
    public $expenses;
    public $balance;
    public $progress;
    public $transactions;
    public $openingBalance;

    public function __construct(
        string $start,
        string $end,
        float $income,
        float $expenses,
        float $balance,
        float $progress,
        array $transactions = [],
        float $openingBalance = 0
    ) {
        $this->start = $start;
        $this->end = $end;
        $this->income = $income;  
        $this->expenses = $expenses;
        $this->balance = $balance;
        $this->progress = $progress;
        $this->transactions = $transactions;
        $this->openingBalance = $openingBalance;   
    }
}
