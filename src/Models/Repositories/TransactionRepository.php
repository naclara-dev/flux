<?php

namespace App\Models\Repositories;

use App\Models\Entities\Transaction;

class TransactionRepository extends AbstractRepository
{
    protected $table = 'transactions';
    protected $entityClass = Transaction::class;
}
