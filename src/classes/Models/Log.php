<?php
namespace App\Models;

class Log extends \App\Model
{
    protected $attributes = ['message', 'type'];

    public function save($exception = false)
    {
        $properties = $this->state;

        list($table_name, $self_class) = self::getTableName();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->setLogging(false);

        $properties['createDate'] = 'NOW()';
        $properties['deleted'] = '0';

        $query->insert($properties);
    }
}
