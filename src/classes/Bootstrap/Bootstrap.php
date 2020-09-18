<?php

namespace App\Bootstrap;

use App\Configuration;
use App\Kernel;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;

class Bootstrap
{
    private static $kernel = null;

    public function startUp()
    {
        $this->setConfigParams();

        \App\Helper\Loggers\Logger::setLogger(
        //new App\Helper\Loggers\FileLogger(ABS_PATH . '/logs/log.txt')
            new \App\Helper\Loggers\DBLogger()
        );

        try {
            $drivers = [
                'mysql' => '\\App\\Database\\MySQL',
                'sqlite' => '\\App\\Database\\SQLite'
            ];

            $driverClass = $drivers[\App\Configuration::get('DB_DRIVER')];
            \App\Registry::setDatabase(new $driverClass);
        } catch (\Exception $e) {
            die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
        }
    }

    private function setConfigParams()
    {
        // TODO: this is bäh:
        if (\App\Configuration::get('DB_DRIVER') === 'sqlite') {
            \App\Models\Model::setQueryBuilder(new \App\QueryBuilder\SQLiteBuilder());
        } else {
            \App\Configuration::set('DB_DRIVER', 'mysql');
            \App\Models\Model::setQueryBuilder(new \App\QueryBuilder\Builder());
        }

        \App\QueryBuilder\Builder::setTablePrefix(\App\Configuration::get('DB_PREFIX'));

        if (\App\Configuration::get('env') === 'dev') {
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            ini_set('display_startup_errors', 0);
            error_reporting(0);
        }
    }

    public function getKernel()
    {
        if (self::$kernel === null) {
            self::$kernel = new Kernel(Configuration::get('env'), Configuration::get('env') === 'dev');
        }
        return self::$kernel;
    }
}
