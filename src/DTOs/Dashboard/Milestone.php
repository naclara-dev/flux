<?php

namespace App\DTOs\Dashboard;

class Milestone {
    public $title;
    public $date;
    public $amount;
    public $type;

    public function __construct(
        string $title,
        string $date,
        float $amount,
        ?string $type = null
    ) {
        $this->title = $title;
        $this->date = $date;
        $this->amount = $amount;
        $this->type = $type;
    }
}
