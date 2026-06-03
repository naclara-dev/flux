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
