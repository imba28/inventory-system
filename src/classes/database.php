<?php
namespace App;
use PDO;

class Database {
    public $dbh;

    public function __construct() {
        $dsn = 'mysql:host='. \App\Configuration::get('DB_HOST') .';dbname='. \App\Configuration::get('DB_DB') .';port='. \App\Configuration::get('DB_PORT') .';';

        $this->dbh = new \PDO($dsn, \App\Configuration::get('DB_USER'), \App\Configuration::get('DB_PWD'));
        if(is_null($this->dbh)) throw new \UnexpectedValueException('Database connection could not be established!');

        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->dbh->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
}
?>