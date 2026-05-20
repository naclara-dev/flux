<?php

namespace App\Models\Repositories;

use App\Models\Entities\EntityType;

class EntityTypeRepository extends Repository
{
    protected $table = 'entity_types';
    protected $entityClass = EntityType::class;
}
