<?php
namespace App;

abstract class Model implements \JsonSerializable
{
    use Traits\GetSetData;
    use Traits\Events;

    protected $id;
    protected $deleted;
    protected $user;

    static private $instances = array();
    static private $relationRules = array();

    public function __construct($options = array())
    {
        foreach ($options as $key => $value) {
            if (preg_match('/([\w]+)_id$/', $key, $m)) {
                $class_name = '\App\Models\\'.ucfirst($m[1]);
                //if(ClassManager::markedNotExisting($class_name) == false) {
                if (class_exists($class_name)) {
                    $key = $m[1];
                    try {
                        $value = $class_name::find($value);
                    } catch (\App\Exceptions\NothingFoundException $e) {
                        \App\Debugger::log("{$class_name} with id {$value} not found!", 'warning');
                        $value = null;
                    }
                }
                //}
            }
            if (property_exists($this, $key)) {
                $this->$key = $value;
                $this->data[$key] = $value;
            }
        }

        if ($this->isCreated()) {
            if (!isset(self::$instances[get_called_class()])) {
                self::$instances[get_called_class()] = array();
            }
            self::$instances[get_called_class()][$this->getId()] = $this;
        }
    }

    public function isCreated()
    {
        return isset($this->id) && !is_null($this->id);
    }

    public function getId()
    {
        return $this->id;
    }

    public function jsonSerialize()
    {
        $json = array();

        foreach ($this->data as $key => $value) {
            if ($key == 'deleted') {
                continue;
            }

            if ($this->data[$key] instanceof \App\Model) {
                $value = $value->jsonSerialize();
            }

            $json[$key] = $value;
        }

        return $json;
    }

    public function remove()
    {
        return self::delete($this->getId());
    }

    public function save($head_column = null, $head_id = null, $exception = false)
    {
        $this->trigger('save');

        //$properties = get_object_vars($this);
        $properties_update = $this->getChangedProperties();
        if (!is_null($head_column) && !is_null($head_id)) {
            $properties_update[$head_column] = $head_id;
        }

        if (count($properties_update) == 0) {
            if ($this->isCreated()) {
                if ($exception) {
                    throw new \App\QueryBuilder\NothingChangedException("nothing changed!");
                }
                return false;
            }
        }

        $query = new QueryBuilder\Builder(self::getTableName());

        if ($this->isCreated()) {
            $res = $query->where('id', '=', $this->id)->update($properties_update);

            if ($res === false) {
                throw new \App\QueryBuilder\QueryBuilderException("could not update data", $query->getError());
            }
        } else {
            $this->trigger('create');

            $properties_update['createDate'] = 'NOW()';
            $properties_update['deleted'] = '0';

            $res = $query->insert($properties_update);

            if ($res === false) {
                throw new \App\QueryBuilder\QueryBuilderException("could not insert data", $query->getError());
            }

            $this->id = $query->lastInsertId();
            $this->data['id'] = $this->id;

            self::$instances[get_called_class()][$this->getId()] = $this;
        }

        return true;
    }

    private function getChangedProperties()
    {
        $properties_update = array();

        foreach ($this->data as $name => $value) {
            if (@$this->$name != $value) { // has changed
                if($value instanceof \App\Model) {
                    if (!$value->isCreated()) {
                        $value->save();
                    }
                    $name = "{$name}_id";
                    $value = $value->getId();
                }
                elseif (is_null($value) || (empty($value) && $value != 0)) {
                    $value = null;
                } elseif ($value === "NOW()") {
                    $date = new \DateTime();
                    $this->set($name, $date->format('Y-m-d H:i:s'));
                }

                $properties_update[$name] = $value;
            }
        }

        return $properties_update;
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
    public static function getQuery(array $filters, $limit = false): \App\QueryBuilder\Builder
    {
        $table_name = self::getTableName();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->where('deleted', '=', '0')->orderBy('id', 'DESC');

        if ($limit !== false) {
            $query->limit($limit);
        }

        if (is_array($filters)) {
            if (count($filters) == 3) {
                $query->where($filters[0], $filters[1], $filters[2]);
            } else {
                foreach ($filters as $filter) {
                    if (count($filter) != 3) {
                        continue;
                    }
                    $query->where($filter[0], $filter[1], $filter[2]);
                }
            }
        }

        return $query;
    }

    public static function getOptions($filters = array(), $all = false, $limit = false, $order = array('id' => 'DESC'))
    {
        $table_name = self::getTableName();
        $self_class = get_called_class();

        $query = new \App\QueryBuilder\Builder($table_name);
        $query->where('deleted', '=', '0');

        foreach ($order as $column => $order) {
            $query->orderBy($column, $order);
        }

        if ($limit == false) {
            if (!$all) {
                $query->limit(1);
            }
        } else {
            $query->limit($limit);
        }

        if (is_array($filters)) {
            if (count($filters) == 3 && is_string($filters[1])) {
                $query->where($filters[0], $filters[1], $filters[2]);
            } else {
                foreach ($filters as $filter) {
                    if (count($filter) != 3) {
                        continue;
                    }
                    $query->where($filter[0], $filter[1], $filter[2]);
                }
            }
        }

        $res = $query->get();

        if (!empty($res)) {
            $options = $all ? $res : current($res);
            return $options;
        }
        throw new \App\Exceptions\NothingFoundException("No entries found for {$self_class}!");
    }

    public static function find($value, $column = 'id'): \App\Model
    {
        $self_class = get_called_class();
        if ($column == 'id' && isset(self::$instances[$self_class][$value])) {
            return self::$instances[$self_class][$value];
        }

        $options = self::getOptions(array(
            array($column, '=', $value)
        ));

        return new $self_class($options);
    }

    public static function findByFilter(array $filters, $limit = false, $order = array('id' => 'DESC'))
    {
        $options = self::getOptions($filters, true, $limit, $order);

        if ($limit === false || $limit !== 1) {
            return new Collection(self::getModelFromOption($options));
        } else {
            return current(self::getModelFromOption($options));
        }
    }

    public static function all(): \Traversable
    {
        $options = self::getOptions(array(), true);

        return new Collection(self::getModelFromOption($options));
    }

    private static function getModelFromOption($arg)
    {
        if (!is_array($arg)) {
            $arg = array($arg);
        }
        if (!isset(self::$instances[get_called_class()])) {
            self::$instances[get_called_class()] = array();
        }

        $models = array();
        $self_class = get_called_class();

        foreach ($arg as $option) {
            if (!isset(self::$instances[$self_class][$option['id']])) {
                self::$instances[$self_class][$option['id']] = new $self_class($option);
                ;
            }

            $models[] = self::$instances[$self_class][$option['id']];
        }

        return $models;
    }

    public static function new(): \App\Model
    {
        $self_class = get_called_class();
        if ($self_class == false) {
            throw new \RuntimeException('Oh shit.');
        }

        return new $self_class();
    }

    public static function delete(int $id)
    {
        try {
            $obj = self::find($id);
            $obj->trigger('delete');
            $obj->set('deleted', 1);

            return $obj->save();
        } catch (\App\Exceptions\NothingFoundException $e) {
            return false;
        }
    }

    public function __get($property)
    {
 // z.B product->images => undefined => __get('images') => load image collection and set property => return Collection
        $self_class = get_called_class();
        if (isset(self::$relationRules[$self_class][$property])) {
            try {
                $this->{$property} = self::$relationRules[$self_class][$property]($this->getId());
            } catch (\App\Exceptions\NothingFoundException $e) {
                $this->{$property} = new Collection();
            }

            return $this->{$property};
        }

        throw new \InvalidOperationException('Oh shit.');
    }

    protected static function getTableName()
    {
        $self_class = get_called_class();
        if ($self_class === false) {
            throw new \UnexpectedValueException('Oh shit.');
        }
        $self_class = strtolower($self_class);

        return preg_replace('/(.+)\\\/', '', $self_class).'s';
    }

    protected static function hasMany($modelName, $alias = null)
    {
 // 1:n relation, other model must have the foreign key
        if (is_null($alias)) {
            $alias = $modelName;
        }
        $self_class = get_called_class();

        if (!isset(self::$relationRules[$self_class])) {
            self::$relationRules[$self_class] = array();
        }

        self::$relationRules[$self_class][$alias] = function ($id) use ($modelName) {
            $fullModelName = "\\App\\Models\\{$modelName}";

            if (class_exists($fullModelName)) {
                $foreignColumn = rtrim(self::getTableName(), 's') . '_id';
                return $fullModelName::findByFilter(array($foreignColumn, '=', $id), false, array('id' => 'ASC'));
            }

            throw new \InvalidArgumentException("Model {$fullModelName} does not exist!");
        };
    }
}
