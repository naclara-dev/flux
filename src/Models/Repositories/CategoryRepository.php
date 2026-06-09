<?php

namespace App\Models\Repositories;

use App\Models\Entities\Category;

class CategoryRepository extends Repository {
    protected $table = 'categories';
    protected $entityClass = Category::class;
}
