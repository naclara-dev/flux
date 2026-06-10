<?php

namespace App\DTOs\Dashboard;

class Milestone {
    public $title;
    public $date;
    public $amount;

    public function __construct(
        string $title,
        string $date,
        float $amount
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->amount = $amount;
    }
}
