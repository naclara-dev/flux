<?php

require_once __DIR__ . '/config/bootstrap.php';

$uri = appCurrentPath();
$base = normalizePath(appBaseUrl());
$bases = [$base];

if (str_ends_with($base, '/public/')) {
    $bases[] = normalizePath(substr($base, 0, -strlen('/public/')));
}

foreach ($bases as $basePath) {
    if ($basePath !== '/' && str_starts_with($uri, $basePath)) {
        $uri = '/' . ltrim(substr($uri, strlen($basePath)), '/');
        $uri = normalizePath($uri);
        break;
    }
}

$routes = require ROUTES_PATH . '/web.php';

if (!isset($routes[$uri])) {
    die('404');
}

[$class, $method] = $routes[$uri];

$controller = new $class($twig);
$controller->$method();
