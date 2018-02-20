<?php
namespace App\Database;

use \PDO;

class SQLite implements DatabaseInterface
{
    private $handler;

    public function __construct()
    {
        $dsn = 'sqlite:' . SRC . '/db/' . \App\Configuration::get('DB_PATH');

        $this->handler = new \PDO($dsn);
        if (is_null($this->handler)) {
            throw new \UnexpectedValueException('Database connection could not be established!');
        }

        $this->handler->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->handler->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getHandler(): PDO
    {
        return $this->handler;
    }
}
