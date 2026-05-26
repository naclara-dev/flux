<?php

require_once __DIR__ . '/../config/bootstrap.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$routes = require ROUTES_PATH . '/web.php';

if (!isset($routes[$uri])) {
    die('404');
}

[$class, $method] = $routes[$uri];

$controller = new $class($twig);
$controller->$method();
