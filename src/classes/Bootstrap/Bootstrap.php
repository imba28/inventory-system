<?php

namespace App\Bootstrap;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Bootstrap
{
    private $container = null;

    private function startUp()
    {
        $this->buildContainer();
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

    public function run() {
        $this->startUp();

        $router = $this->container->get("App\Routing\Router");

        require ABS_PATH . '/src/config/routes.php';
        $router->route();
    }

    private function buildContainer()
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__));
        $loader->load('../../config/services.yml');

        $containerBuilder->compile();

        $this->container = $containerBuilder;
    }

    private function setConfigParams()
    {
        // TODO: this is bäh:
        if (\App\Configuration::get('DB_DRIVER') === 'sqlite') {
            \App\Models\Model::setQueryBuilder(new \App\QueryBuilder\SQLiteBuilder());
        } else {
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
}
