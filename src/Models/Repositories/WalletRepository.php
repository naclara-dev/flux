<?php

namespace App\Models\Repositories;

use App\Models\Entities\Wallet;

class WalletRepository extends Repository
{
    protected $table = 'wallets';
    protected $entityClass = Wallet::class;
}
