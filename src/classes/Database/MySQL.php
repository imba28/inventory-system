<?php
namespace App\Database;

use App\Models\Model;
use App\QueryBuilder\Builder;
use \PDO;

class MySQL implements DatabaseInterface
{
    private $handler;

    public function __construct(string $host, string $database, string $user, ?string $password, int $port, Builder $builder)
    {
        $dsn =
            'mysql:host='.
            $host .
            ';dbname='.
            $database .
            ';port='. $port .
            ';';

        $this->handler = new \PDO($dsn, $user, $password);
        if (is_null($this->handler)) {
            throw new \UnexpectedValueException('Database connection could not be established!');
        }

        $this->handler->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $this->handler->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->handler->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->handler->exec('SET NAMES UTF8');

        Model::setQueryBuilder($builder);
    }

    public function getHandler(): PDO
    {
        return $this->handler;
    }
}
