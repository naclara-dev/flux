<?php

use App\Controllers\HomeController;
use App\Controllers\ManageController;
use App\Controllers\SettingsController;
use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;

return [
    '/'                      => [HomeController::class, 'index'],
    '/manage/'               => [ManageController::class, 'index'],
    '/manage/categories/'    => [ManageController::class, 'categories'],
    '/manage/categories/store/' => [CategoryController::class, 'store'],
    '/manage/wallets/'       => [ManageController::class, 'wallets'],
    '/manage/entities/'      => [ManageController::class, 'entities'],
    '/manage/rules/'         => [ManageController::class, 'rules'],
    '/settings/'             => [SettingsController::class, 'index'],
    '/login/'                => [AuthController::class, 'login'],
    '/login/check-email/'    => [AuthController::class, 'checkEmail'],
    '/logoff/'               => [AuthController::class, 'logoff'],
    '/account/'              => [AccountController::class, 'index'],
    '/register/'             => [AccountController::class, 'store'],
    '/register/check-email/' => [AccountController::class, 'checkEmail'],
];
