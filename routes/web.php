<?php

use App\Controllers\HomeController;
use App\Controllers\ManageController;
use App\Controllers\SettingsController;
use App\Controllers\AccountController;

$root = appBaseUrl() . '/';

return [
    "$root"            => [HomeController::class, 'index'],
    "{$root}manage/"   => [ManageController::class, 'index'],
    "{$root}settings/" => [SettingsController::class, 'index'],
    "{$root}account/"  => [AccountController::class, 'index']
];
