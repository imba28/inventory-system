<?php
namespace App\Models;

class Log extends \App\Model {
    protected $message;
    protected $type;

    public function save() {
        $properties = $this->data;

        list($table_name, $self_class) = self::getTableName();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->setLogging(false);

        $properties['createDate'] = 'NOW()';
        $properties['deleted'] = '0';

        $query->insert($properties);
    }
}