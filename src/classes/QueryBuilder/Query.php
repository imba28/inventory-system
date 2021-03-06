<?php
namespace App\QueryBuilder;

use \PDO;
use App\Helper\Loggers\Logger;

class Query
{
    protected $sql = null;
    protected $logging;

    protected $result = null;
    protected $lastInsertId = null;

    public function __construct($sql, $logging = true)
    {
        $this->sql = $sql;
        $this->logging = $logging;
    }

    public function execute(array $columns)
    {
        $dbh = \App\Registry::getDatabase();

        try {
            $sth = $dbh->prepare($this->sql);

            if (!$sth) {
                if ($this->logging) {
                    $this->log("Prepare() fehlgeschlagen!", $this->sql, array_values($columns));
                }
                throw new \Exception("prepare() fehlgeschlagen!");
            }

            $sth->execute(array_values($columns));

            $this->result = $sth->fetchAll(\PDO::FETCH_ASSOC);
            $this->lastInsertId = $dbh->lastInsertId();
        } catch (\Exception $e) {
            if ($this->logging) {
                $this->log($e->getMessage(), $this->sql, array_values($columns));
            }
            throw new \App\QueryBuilder\QueryBuilderException($e->getMessage());
        }
        return true;
    }

    public function lastInsertID()
    {
        return $this->lastInsertId;
    }

    public function getResult()
    {
        if (!is_null($this->result)) {
            return $this->result;
        }
        throw new \Exception("Query must be executed first!");
    }

    final protected function log($error, $sql, array $values = null)
    {
        $errorMessage = "Fehler: " . $error . "\nCallstack:\n";
        $errorMessage .= generateCallTrace();
        $errorMessage .= "\nSql: $sql";
        if (!is_null($values)) {
            $errorMessage .= "\nValues: ". join(", ", $values);
        }

        Logger::error($errorMessage);
    }
}
