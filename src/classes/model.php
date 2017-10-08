<?php
namespace App;

abstract class Model {
    //use Traits\GetSet;
    protected $id;

    public static function get($value, $column = 'id') {
        $self_class = get_called_class();
        if($self_class == false) throw new RuntimeException('Oh shit.');

        return new $self_class($value);
    }

    public function save() {
        $properties = get_object_vars($this);
        $self_class = strtolower(get_called_class());
        $table_name = preg_replace('/(.+)\\\/', '', $self_class).'s';

        $query = new QueryBuilder\Builder($table_name);

        if($this->isCreated()) {
            if(!$query->where('id', '=', $this->id)->update($properties)) throw new App\QueryBuilder\QueryBuilderException("could not update data: {$query->getError()}");
        }
        else {
            if(!$query->insert($properties)) throw new App\QueryBuilder\QueryBuilderException("could not insert data: {$query->getError()}");
        }
    }

    public function isCreated() {
        return is_null($this->id);
    }
}
?>