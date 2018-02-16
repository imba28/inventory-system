<?php
namespace App\Models;

class Log extends \App\Model
{
    protected $attributes = ['message', 'type'];

    public function save($exception = false)
    {
        $properties = $this->state;

        $query = new \App\QueryBuilder\Builder(self::getTableName());
        $query->setLogging(false);

        $properties['createDate'] = 'NOW()';
        $properties['deleted'] = '0';

        $query->insert($properties);
    }
}
