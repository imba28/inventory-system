<?php
namespace App;

abstract class Model {
    use Traits\GetSet;

    protected $id;

    public function __construct($options = array()) {
        foreach($options as $key => $value) {
            if(property_exists($this, $key)) $this->$key = $value;
        }
    }

    public function isCreated() {
        return isset($this->id) && !is_null($this->id);
    }

    public function save() {
        $properties = get_object_vars($this);

        foreach($properties as $name => $obj) {
            if($obj instanceof \App\Model) {
                if(!$obj->isCreated()) {
                    $obj->save();
                }
                $properties["{$name}_id"] = $obj->get('id');
                unset($properties[$name]);
            }
            elseif($name == 'id') {
                unset($properties[$name]);
            }
        }

        list($table_name, $self_class) = self::getTableName();

        $query = new QueryBuilder\Builder($table_name, true);

        if($this->isCreated()) {
            $properties['stamp'] = 'NOW()'; // set last update date

            if(!$query->where('id', '=', $this->id)->update($properties)) throw new \App\QueryBuilder\QueryBuilderException("could not update data: {$query->getError()}");
        }
        else {
            $properties['user_id'] = 1;
            $properties['createDate'] = 'NOW()';

            $res = $query->insert($properties);

            if(!$res) throw new \App\QueryBuilder\QueryBuilderException("could not insert data: {$query->getError()}");

            $this->id = $query->lastInsertId();
        }

        return true;
    }

    /*
    Doof, weil das nur mit primitiven Datentypen funktioniert....
    public function refresh() {
        list($options) = self::getOptions($this->id);
        foreach($options as $key => $value) {
            $this->$key = $value;
        }
    }*/

    // Static methods.
    public static function getOptions($value, $column = 'id', $all = false) {
        list($table_name, $self_class) = self::getTableName();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->where($column, '=', $value)->where('deleted', '=', '0');
        if(!$all) $query->limit(1);

        $res = $query->get();
        if(!empty($res)) {
            $options = $all ? $res : current($res);
            return array($options, $self_class);
        }
        throw new \InvalidArgumentException("{$self_class} with {$column} = {$value} does not exists!");
    }

    public static function grab($value, $column = 'id') {
        list($options, $self_class) = self::getOptions($value, $column);
        return new $self_class($options);
    }

    public static function grabAll($value, $column = 'id') {
        list($options, $self_class) = self::getOptions($value, $column, true);

        $objs = array();
        foreach($options as $o) {
            $objs[] = new $self_class($o);
        }
        return $objs;
    }

    public static function all() {
        
    }

    public static function new() {
        $self_class = get_called_class();
        if($self_class == false) throw new \RuntimeException('Oh shit.');

        return new $self_class();
    }

    public static function delete(integer $id) {
        $obj = self::grab($id);
        $obj->set('deleted', 1);
        $obj->save();
    }

    private static function getTableName() {
        $self_class = get_called_class();
        if($self_class === false) throw new \UnexpectedValueException('Oh shit.');

        $self_class = strtolower($self_class);
        return array(preg_replace('/(.+)\\\/', '', $self_class).'s', $self_class);
    }

}
?>