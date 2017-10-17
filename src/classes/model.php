<?php
namespace App;

abstract class Model {
    use Traits\GetSetData;
    use Traits\Events;

    protected $id;
    protected $deleted;

    public function __construct($options = array()) {
        foreach($options as $key => $value) {
            if(preg_match('/([\w]+)_id$/', $key, $m)) {
                $class_name = '\App\Models\\'.$m[1];
                if(ClassManager::markedNotExisting($class_name) == false) {
                    if(class_exists($class_name)) {
                        $key = $m[1];
                        $value = $class_name::grab($value);
                    }
                }
            }
            if(property_exists($this, $key)) {
                $this->$key = $value;
                $this->data[$key] = $value;
            }
        }
    }

    public function isCreated() {
        return isset($this->id) && !is_null($this->id);
    }

    public function getId() {
        return $this->id;
    }

    public function save() {
        $this->trigger('save');

        //$properties = get_object_vars($this);
        $properties_update = $this->data;

        foreach($properties_update as $name => $value) {
            if($this->$name != $value) { // has changed
                if($name == 'id') {
                    unset($properties_update[$name]);
                }
                elseif(is_null($value) || (empty($value) && $value != 0)) {
                    $properties_update[$name] = null;
                }
                elseif($value instanceof \App\Model) {
                    if(!$value->isCreated()) {
                        $value->save();
                    }
                    $properties_update["{$name}_id"] = $value->get('id');
                    unset($properties_update[$name]);
                }
            }
            else {
                unset($properties_update[$name]);
            }
        }

        if(count($properties_update) == 0) throw new \App\QueryBuilder\NothingChangedException("nothing changed!");

        list($table_name, $self_class) = self::getTableName();

        $query = new QueryBuilder\Builder($table_name);

        if($this->isCreated()) {
            //$properties['stamp'] = 'NOW()'; // set last update date
            $res = $query->where('id', '=', $this->id)->update($properties_update);
            if($res === false) {
                throw new \App\QueryBuilder\QueryBuilderException("could not update data: {$query->getError()}");
            }
        }
        else {
            $this->trigger('create');

            $properties_update['user_id'] = 1;
            $properties_update['createDate'] = 'NOW()';
            $properties_update['deleted'] = '0';

            $res = $query->insert($properties_update);

            if($res === false) {
                throw new \App\QueryBuilder\QueryBuilderException("could not insert data: {$query->getError()}");
            }

            $this->id = $query->lastInsertId();
            $this->data['id'] = $this->id;
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
    public static function getOptions($filters = array(), $all = false) {
        list($table_name, $self_class) = self::getTableName();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->where('deleted', '=', '0')->orderBy('id', 'DESC');

        if(!$all) $query->limit(1);

        if(is_array($filters)) {
            foreach($filters as $filter) {
                if(count($filter) != 3) continue;
                $query->where($filter[0], $filter[1], $filter[2]);
            }
        }

        $res = $query->get();

        if(!empty($res)) {
            $options = $all ? $res : current($res);
            return array($options, $self_class);
        }
        throw new \InvalidArgumentException("No entries found for {$self_class}!");
    }

    public static function grab($value, $column = 'id') {
        list($options, $self_class) = self::getOptions(array(
            array($column, '=', $value)
        ));
        return new $self_class($options);
    }

    public static function grabByFilter(array $filters) {
        list($options, $self_class) = self::getOptions($filters, true);

        $objs = array();
        foreach($options as $o) {
            $objs[] = new $self_class($o);
        }
        return $objs;
    }

    public static function grabAll() {
        list($options, $self_class) = self::getOptions(array(), true);

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
        $this->trigger('delete');

        $obj = self::grab($id);
        $obj->set('deleted', 1);
        $obj->save();
    }

    protected static function getTableName() {
        $self_class = get_called_class();
        if($self_class === false) throw new \UnexpectedValueException('Oh shit.');

        $self_class = strtolower($self_class);
        return array(preg_replace('/(.+)\\\/', '', $self_class).'s', $self_class);
    }

}
?>