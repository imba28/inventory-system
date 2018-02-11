<?php
namespace App;

abstract class Model implements \JsonSerializable
{
    use Traits\Events;

    protected $attributes = [];

    protected $originalState = [];
    protected $state = [];

    private $foreignKey;

    static private $instances = array();
    static private $relationRules = array();

    public function __construct($options = array())
    {
        $this->originalState = array_merge($this->attributes, ['id', 'user', 'createDate', 'stamp', 'deleted']);
        $this->originalState = array_map(function() { return null; }, array_flip($this->originalState));

        foreach ($options as $key => $value) {
            if (preg_match('/([\w]+)_id$/', $key, $m)) {
                $class_name = '\App\Models\\'.ucfirst($m[1]);
                if (class_exists($class_name)) {
                    $key = $m[1];
                    try {
                        $value = $class_name::find($value);
                    } catch (\App\Exceptions\NothingFoundException $e) {
                        \App\Debugger::log("{$class_name} with id {$value} not found!", 'warning');
                        $value = null;
                    }
                }
            }

            if (array_key_exists($key, $this->originalState)) {
                $this->originalState[$key] = $value;
                $this->state[$key] = $value;
            }
        }

        if ($this->isCreated()) {
            if (!isset(self::$instances[get_called_class()])) {
                self::$instances[get_called_class()] = array();
            }
            self::$instances[get_called_class()][$this->getId()] = $this;
        }

        $this->init();
    }

    protected function init() {}

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

        array_walk($properties_update, function ($value, $key) {
            $this->{$key} = $value;
        });

        return true;
    }

    public function set($property, $value)
    {
        if (array_key_exists($property, $this->originalState)) {
            $this->state[$property] = $value;
            $this->trigger('set', $this, array('property' => $property, 'value' => $value));
            return true;
        }
        return false;
    }

    public function get($property)
    {
        if (array_key_exists($property, $this->originalState)) {
            return $this->state[$property];
        }
        return null;
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

    public function getForeignKey(): string
    {
        if(!isset($this->foreignKey)) {
            $namespaceParts = explode('\\', get_called_class());
            $this->foreignKey = strtolower($namespaceParts[count($namespaceParts) - 1] . '_id');
        }
        return $this->foreignKey;
    }

    private function getChangedProperties()
    {
        $properties_update = array();

        foreach ($this->state as $name => $value) {
            if (@$this->originalState[$name] != $value) { // has changed
                if ($value instanceof \App\Model) {
                    if (!$value->isCreated()) {
                        $value->save();
                    }
                    $name = "{$name}_id";
                    $value = $value->getId();
                } elseif (is_null($value) || (empty($value) && $value != 0)) {
                    $value = null;
                } elseif ($value === "NOW()") {
                    $date = new \DateTime();
                    $value = $date->format('Y-m-d H:i:s');
                    $this->data[$name] = $value;
                }

                $properties_update[$name] = $value;
            }
        }

        return $properties_update;
    }

    /*
    Doof, weil das nur mit primitiven Datentypen funktioniert....
    public function refresh() {
        list($options) = self::getModelData($this->id);
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

        return $query;
    }

    public static function getModelData(array $filters, $limit = false, $order = array('id' => 'DESC'))
    {
        $query = new \App\QueryBuilder\Builder(self::getTableName());
        $query->where('deleted', '=', '0');

        if ($limit !== false) {
            $query->limit($limit);
        }

        foreach ($order as $column => $order) {
            $query->orderBy($column, $order);
        }

        if (count($filters) == 3 && is_string($filters[1])) {
            $filters = array($filters);
        }

        foreach ($filters as $filter) {
            if (count($filter) == 3) {
                $query->where($filter[0], $filter[1], $filter[2]);
            }
        }

        $res = $query->get();
        if (!empty($res)) {
            return $limit == 1 ? current($res) : $res;
        } else {
            throw new \App\Exceptions\NothingFoundException('No entries found for '. get_called_class() . '!');
        }
    }

    public static function find($value, $column = 'id'): \App\Model
    {
        $self_class = get_called_class();
        if ($column == 'id' && isset(self::$instances[$self_class][$value])) {
            return self::$instances[$self_class][$value];
        }

        $options = self::getModelData(array(
            array($column, '=', $value)
        ), 1);

        return new $self_class($options);
    }

    public static function findByFilter(array $filters, $limit = false, $order = array('id' => 'DESC'))
    {
        $data = self::getModelData($filters, $limit, $order);

        if ($limit === false || $limit !== 1) {
            $collection = new Collection();

            foreach ($data as $option) {
                $collection->append(self::getModelFromOption($option));
            }

            return $collection;
        } else {
            return self::getModelFromOption($data);
        }
    }

    public static function all(): \Traversable
    {
        $options = self::getModelData(array());
        $collection = new Collection();

        foreach ($options as $option) {
            $collection->append(self::getModelFromOption($option));
        }

        return $collection;
    }

    private static function getModelFromOption(array $data)
    {
        if (!isset(self::$instances[get_called_class()])) {
            self::$instances[get_called_class()] = array();
        }

        $self_class = get_called_class();

        if (!isset(self::$instances[$self_class][$data['id']])) {
            self::$instances[$self_class][$data['id']] = new $self_class($data);
        }

        return self::$instances[$self_class][$data['id']];
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
