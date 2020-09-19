<?php

namespace App\Bootstrap;

use App\Configuration;
use App\Database\DatabaseInterface;
use App\Kernel;

class Bootstrap
{
    private static $kernel = null;

    private $database;


    public function __construct(DatabaseInterface $database)
    {
        $this->database = $database;
    }

    public function startUp()
    {
        $this->setConfigParams();

        \App\Helper\Loggers\Logger::setLogger(
        //new App\Helper\Loggers\FileLogger(ABS_PATH . '/logs/log.txt')
            new \App\Helper\Loggers\DBLogger()
        );

        try {
            \App\Registry::setDatabase($this->database);
        } catch (\Exception $e) {
            die("Keine Verbindung zur Datenbank möglich:". $e->getMessage());
        }
    }

    private function setConfigParams()
    {
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
