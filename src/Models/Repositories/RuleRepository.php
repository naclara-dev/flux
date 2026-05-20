<?php

namespace App\Models\Repositories;

use App\Models\Entities\Rule;

class RuleRepository extends AbstractRepository
{
    protected $table = 'rules';
    protected $entityClass = Rule::class;
}
