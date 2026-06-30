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
use App\Controllers\TransactionController;

return [
    '/'                      => [HomeController::class, 'index'],
    '/dashboard/cycle/'      => [HomeController::class, 'cycle'],
    '/dashboard/cycle/print/' => [HomeController::class, 'printCycle'],
    // Define a rota que gera previsoes periodicas do ciclo atual
    '/dashboard/periodic/generate/' => [HomeController::class, 'generatePeriodicTransactions'],
    // Define a rota que encerra o ciclo atual
    '/dashboard/cycle/close/' => [HomeController::class, 'closeCurrentCycle'],
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
    '/transactions/find/'     => [TransactionController::class, 'find'],
    '/transactions/store/'    => [TransactionController::class, 'store'],
    '/transactions/delete/'   => [TransactionController::class, 'delete'],
    '/settings/'             => [SettingsController::class, 'index'],
    '/settings/store/'       => [SettingsController::class, 'store'],
    '/login/'                => [AuthController::class, 'login'],
    '/login/google/'         => [AuthController::class, 'googleRedirect'],
    '/login/google/callback/' => [AuthController::class, 'googleCallback'],
    '/login/check-email/'    => [AuthController::class, 'checkEmail'],
    '/logoff/'               => [AuthController::class, 'logoff'],
    '/account/'              => [AccountController::class, 'index'],
    '/account/update/'       => [AccountController::class, 'update'],
    '/register/'             => [AccountController::class, 'store'],
    '/register/check-email/' => [AccountController::class, 'checkEmail'],
];
