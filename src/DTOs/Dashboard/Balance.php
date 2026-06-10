<?php 

namespace App\DTOs\Dashboard;

class Balance {
    public $current;
    public $commited;
    public $spendable;

    public function __construct(
        float $current,
        float $commited,
        float $spendable
    ) {
        $this->current = $current;
        $this->commited = $commited;
        $this->spendable = $spendable;     
    }
}
