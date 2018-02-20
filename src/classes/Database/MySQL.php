<?php
namespace App\Database;

use \PDO;

class MySQL implements DatabaseInterface
{
    private $handler;

    public function __construct()
    {
        $dsn =
            'mysql:host='.
            \App\Configuration::get('DB_HOST') .
            ';dbname='.
            \App\Configuration::get('DB_DB') .
            ';port='. \App\Configuration::get('DB_PORT') .
            ';';

        $this->handler = new \PDO($dsn, \App\Configuration::get('DB_USER'), \App\Configuration::get('DB_PWD'));
        if (is_null($this->handler)) {
            throw new \UnexpectedValueException('Database connection could not be established!');
        }

        $this->handler->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->handler->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->handler->exec('SET NAMES UTF8');
    }

    public function getHandler(): PDO
    {
        return $this->handler;
    }
}
