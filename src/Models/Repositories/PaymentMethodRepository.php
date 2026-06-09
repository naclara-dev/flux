<?php

namespace App\Models\Repositories;

use App\Models\Entities\PaymentMethod;

class PaymentMethodRepository extends Repository
{
    protected $table = 'payment_methods';
    protected $entityClass = PaymentMethod::class;
}
