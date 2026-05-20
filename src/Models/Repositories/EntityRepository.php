<?php

namespace App\Models\Repositories;

use App\Models\Entities\Entity;

class EntityRepository extends Repository
{
    protected $table = 'entities';
    protected $entityClass = Entity::class;
}
