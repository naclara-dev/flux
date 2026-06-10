<?php

namespace App\DTOs\Dashboard;

class Dashboard {
    public $balance;
    public $currentCycle;
    public $nextCycle;
    public $nextIncome;
    public $milestones;

    public function __construct(
        Balance $balance,
        Cycle $currentCycle,
        Cycle $nextCycle,
        NextIncome $nextIncome,
        array $milestones
    ) {
        $this->balance = $balance;
        $this->currentCycle = $currentCycle;
        $this->nextCycle = $nextCycle;
        $this->nextIncome = $nextIncome;
        $this->milestones = $milestones;
    }
}
