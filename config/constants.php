<?php

defined('BASE_PATH') || define('BASE_PATH', dirname(__DIR__));
defined('PUBLIC_PATH') || define('PUBLIC_PATH', BASE_PATH . '/public');
defined('SRC_PATH') || define('SRC_PATH', BASE_PATH . '/src');
defined('CONTROLLERS_PATH') || define('CONTROLLERS_PATH', SRC_PATH . '/Controllers');
defined('MODELS_PATH') || define('MODELS_PATH', SRC_PATH . '/Models');
defined('VIEWS_PATH') || define('VIEWS_PATH', SRC_PATH . '/Views');
defined('ASSETS_PATH') || define('ASSETS_PATH', BASE_PATH . '/assets');
defined('CONFIG_PATH') || define('CONFIG_PATH', BASE_PATH . '/config');
defined('DATABASE_PATH') || define('DATABASE_PATH', BASE_PATH . '/database');
defined('MIGRATIONS_PATH') || define('MIGRATIONS_PATH', DATABASE_PATH . '/migrations');
defined('SEEDS_PATH') || define('SEEDS_PATH', DATABASE_PATH . '/seeds');
