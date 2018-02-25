<?php
session_start();

require_once 'lib/functions.php';

require ABS_PATH . '/vendor/autoload.php';
require_once ABS_PATH . '/src/config/config.php';

App\QueryBuilder\Builder::setTablePrefix(\App\Configuration::get('DB_PREFIX'));

// TODO: this is bäh:
if (App\Configuration::get('DB_DRIVER') === 'sqlite') {
    App\Models\Model::setQueryBuilder(new \App\QueryBuilder\SQLiteBuilder());
} else {
    App\Models\Model::setQueryBuilder(new \App\QueryBuilder\Builder());
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

\App\Helper\Loggers\Logger::setLogger(
    //new App\Helper\Loggers\FileLogger(ABS_PATH . '/logs/log.txt')
    new App\Helper\Loggers\DBLogger()
);

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

$router = App\Routing\Router::getInstance();
require ABS_PATH . '/src/config/routes.php';
