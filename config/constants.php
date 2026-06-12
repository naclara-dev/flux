<?php

define('BASE_PATH', dirname(__DIR__));
define('PUBLIC_PATH', BASE_PATH);
define('ROUTES_PATH', BASE_PATH . '/routes');
define('SRC_PATH', BASE_PATH . '/src');
define('CONTROLLERS_PATH', SRC_PATH . '/Controllers');
define('MODELS_PATH', SRC_PATH . '/Models');
define('VIEWS_PATH', SRC_PATH . '/Views');
define('ASSETS_PATH', BASE_PATH . '/assets');
define('CONFIG_PATH', BASE_PATH . '/config');
define('DATABASE_PATH', BASE_PATH . '/database');
define('MIGRATIONS_PATH', DATABASE_PATH . '/migrations');
define('SEEDS_PATH', DATABASE_PATH . '/seeds');

require_once __DIR__ . '/functions.php';
