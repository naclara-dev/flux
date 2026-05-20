<?php

namespace App\Models\Repositories;

use App\Models\Entities\WalletType;

class WalletTypeRepository extends Repository
{
    protected $table = 'wallet_types';
    protected $entityClass = WalletType::class;
}
