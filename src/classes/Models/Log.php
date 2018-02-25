<?php
namespace App\Models;

class Log extends Model
{
    protected $attributes = ['message', 'type'];

    public function save($exception = false): bool
    {
        $properties = $this->state;

        $query = new \App\QueryBuilder\Builder(self::getTableName());
        $query->setLogging(false);

        $properties['createDate'] = 'NOW()';
        $properties['deleted'] = '0';

        return $query->insert($properties);
    }
}
