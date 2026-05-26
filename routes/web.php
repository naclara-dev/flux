<?php

use App\Controllers\HomeController;

return [
    '/flux/public/' => [HomeController::class, 'index']
];