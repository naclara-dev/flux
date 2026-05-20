<?php

namespace App\Models\Repositories;

use App\Models\Entities\Frequency;

class FrequencyRepository extends Repository
{
    protected $table = 'frequencies';
    protected $entityClass = Frequency::class;
}
