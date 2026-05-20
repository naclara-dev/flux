<?php

namespace App\Models\Repositories;

use App\Models\Entities\User;

class UserRepository extends Repository
{
    protected $table = 'users';
    protected $entityClass = User::class;
}
