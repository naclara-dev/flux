<?php

namespace App\Models\Repositories;

use App\Models\Entities\Rule;

class RuleRepository extends Repository
{
    protected $table = 'rules';
    protected $entityClass = Rule::class;
}
