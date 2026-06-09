<?php

use App\Controllers\HomeController;
use App\Controllers\ManageController;
use App\Controllers\SettingsController;
use App\Controllers\AccountController;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\WalletController;
use App\Controllers\EntityController;
use App\Controllers\TemplateController;

return [
    '/'                      => [HomeController::class, 'index'],
    '/manage/'               => [ManageController::class, 'index'],
    '/manage/categories/'    => [ManageController::class, 'categories'],
    '/manage/categories/store/' => [CategoryController::class, 'store'],
    '/manage/wallets/'       => [ManageController::class, 'wallets'],
    '/manage/wallets/find/' => [WalletController::class, 'find'],
    '/manage/wallets/store/' => [WalletController::class, 'store'],
    '/manage/wallets/delete/' => [WalletController::class, 'delete'],
    '/manage/entities/'      => [ManageController::class, 'entities'],
    '/manage/entities/find/' => [EntityController::class, 'find'],
    '/manage/entities/store/' => [EntityController::class, 'store'],
    '/manage/entities/delete/' => [EntityController::class, 'delete'],
    '/manage/templates/'     => [ManageController::class, 'templates'],
    '/manage/templates/find/' => [TemplateController::class, 'find'],
    '/manage/templates/store/' => [TemplateController::class, 'store'],
    '/manage/templates/delete/' => [TemplateController::class, 'delete'],
    '/settings/'             => [SettingsController::class, 'index'],
    '/login/'                => [AuthController::class, 'login'],
    '/login/check-email/'    => [AuthController::class, 'checkEmail'],
    '/logoff/'               => [AuthController::class, 'logoff'],
    '/account/'              => [AccountController::class, 'index'],
    '/register/'             => [AccountController::class, 'store'],
    '/register/check-email/' => [AccountController::class, 'checkEmail'],
];
