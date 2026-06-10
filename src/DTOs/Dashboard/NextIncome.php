<?php

namespace App\DTOs\Dashboard;

class NextIncome {
    public $date;
    public $amount;

    public function __construct(
        ?string $date,
        float $amount
    ) {
        $this->date = $date;
        $this->amount = $amount;
    }
}
