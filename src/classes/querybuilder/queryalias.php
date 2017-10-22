<?php
namespace App\QueryBuilder;

class QueryAlias {
    use \App\Traits\getSet;

    protected $name;
    protected $alias;

    public function __construct($name, $alias) {
        $this->name = $name;
        $this->alias = $alias;
    }
}
?>