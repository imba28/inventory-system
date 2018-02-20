<?php
session_start();

require_once('lib/functions.php');

require(ABS_PATH . '/vendor/autoload.php');
require_once(ABS_PATH . '/src/config/config.php');

App\QueryBuilder\Builder::setTablePrefix(\App\Configuration::get('DB_PREFIX'));

// TODO: this is bäh:
if (App\Configuration::get('DB_DRIVER') === 'sqlite') {
    App\Model::setQueryBuilder(new \App\QueryBuilder\SQLiteBuilder());
} else {
    App\Model::setQueryBuilder(new \App\QueryBuilder\Builder());
}

if (App\Configuration::get('env') === 'dev') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
}

try {
    $drivers = [
        'mysql' => '\\App\\Database\\MySQL',
        'sqlite' => '\\App\\Database\\SQLite'
    ];

    $driverClass = $drivers[App\Configuration::get('DB_DRIVER')];
    \App\Registry::setDatabase(new $driverClass);
} catch (Exception $e) {
    die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
}

$router = App\Router::getInstance();
include(ABS_PATH . '/src/config/routes.php');
