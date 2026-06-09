<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Initialize Dotenv
$dotenv = \Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->safeLoad();

// Initialize Twig
$loader = new \Twig\Loader\FilesystemLoader(VIEWS_PATH);
$twig = new \Twig\Environment($loader, [
    'cache' => false,
    'debug' => true,
]);

// Initialize Session
\App\Core\Session::start();

$twig->addGlobal('base_url', appBaseUrl());
$twig->addGlobal('current_path', appCurrentPath());

if (\App\Core\Session::has('user_id')) {
    $twig->addGlobal('transaction_wallets', (new \App\Models\Repositories\WalletRepository)->allFromUser());
    $twig->addGlobal('transaction_categories', (new \App\Models\Repositories\CategoryRepository)->allFromUser());
    $twig->addGlobal('transaction_entities', (new \App\Models\Repositories\EntityRepository)->allFromUser());
    $twig->addGlobal('transaction_templates', (new \App\Models\Repositories\TemplateRepository)->allFromUser());
    $twig->addGlobal('transaction_payment_methods', (new \App\Models\Repositories\PaymentMethodRepository)->all());
}
