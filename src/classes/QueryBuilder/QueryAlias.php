<?php
namespace App\QueryBuilder;

class QueryAlias
{
    use \App\Traits\GetSet;

    protected $name;
    protected $alias;

    public function __construct($name, $alias)
    {
        $this->name = $name;
        $this->alias = $alias;
    }
}
