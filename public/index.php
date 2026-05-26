<?php

require_once __DIR__ . '/../config/bootstrap.php';

$uri = appCurrentPath();
$base = normalizePath(appBaseUrl());

if ($base !== '/' && str_starts_with($uri, $base)) {
    $uri = '/' . ltrim(substr($uri, strlen($base)), '/');
    $uri = normalizePath($uri);
}

$routes = require ROUTES_PATH . '/web.php';

if (!isset($routes[$uri])) {
    die('404');
}

[$class, $method] = $routes[$uri];

$controller = new $class($twig);
$controller->$method();
