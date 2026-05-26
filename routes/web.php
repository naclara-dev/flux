<?php

use App\Controllers\HomeController;
use App\Controllers\ManageController;
use App\Controllers\SettingsController;
use App\Controllers\AccountController;

return [
    '/'                    => [HomeController::class, 'index'],
    '/manage/'             => [ManageController::class, 'index'],
    '/manage/categories/'  => [ManageController::class, 'categories'],
    '/manage/wallets/'     => [ManageController::class, 'wallets'],
    '/manage/entities/'    => [ManageController::class, 'entities'],
    '/manage/rules/'       => [ManageController::class, 'rules'],
    '/settings/'           => [SettingsController::class, 'index'],
    '/account/'            => [AccountController::class, 'index'],
];
