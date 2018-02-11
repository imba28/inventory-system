<?php
namespace App;

abstract class Model implements \JsonSerializable
{
    use Traits\Events;

    protected $attributes = [];

    protected $originalState = [];
    protected $state = [];

    private $foreignKey;
    private $relations = [];

    static private $instances = array();

    public function __construct($options = array())
    {
        $this->originalState = array_merge($this->attributes, ['id', 'user', 'createDate', 'stamp', 'deleted']);
        $this->originalState = array_map(function () {
            return null;
        }, array_flip($this->originalState));

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

    protected function init()
    {
    }

    public function isCreated()
    {
        return !empty($this->getId());
    }

    public function getId()
    {
        return $this->get('id');
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
            $res = $query->where('id', '=', $this->getId())->update($properties_update);

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

            $this->originalState['id'] = $query->lastInsertId();
            $this->state['id'] = $query->lastInsertId();

            self::$instances[get_called_class()][$this->getId()] = $this;
        }

        array_walk($this->relations, function ($collection) {
            $collection->save();
        });

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

    public function get($property = null)
    {
        if(is_null($property)) return $this->state;

        if (array_key_exists($property, $this->state)) {
            return $this->state[$property];
        }
        return null;
    }

    public function jsonSerialize()
    {
        $json = array();

        foreach ($this->state as $key => $value) {
            if ($key == 'deleted') {
                continue;
            }

            if ($this->state[$key] instanceof \App\Model) {
                $value = $value->jsonSerialize();
            }

            $json[$key] = $value;
        }

        return $json;
    }

    public function getForeignKey(): string
    {
        if (!isset($this->foreignKey)) {
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
                    $this->state[$name] = $value;
                }

                $properties_update[$name] = $value;
            }
        }

        return $properties_update;
    }

    // PUBLIC STATIC API
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
        if ($limit === false || $limit !== 1) {
            $collection = new Collection();

            try {
                foreach (self::getModelData($filters, $limit, $order) as $option) {
                    $collection->append(self::getModelFromOption($option));
                }
            } catch (\App\Exceptions\NothingFoundException $e) {
            }

            return $collection;
        } else {
            return self::getModelFromOption(self::getModelData($filters, $limit, $order));
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

    public static function new(): Model
    {
        $self_class = get_called_class();
        if ($self_class == false) {
            throw new \RuntimeException('Oh shit.');
        }

        return new $self_class();
    }

    public static function create(array $data): Model
    {
        $obj = self::new();
        foreach ($data as $key => $value) {
            $obj->set($key, $value);
        }
        $obj->save();
        return $obj;
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

    // RELATIONSHIP METHODS
    protected function hasMany($modelName, $foreignKey = null, $localKey = null): Collection
    {
        if (isset($this->relations["{$modelName}_{$foreignKey}_hasmany"])) {
            return $this->relations["{$modelName}_{$foreignKey}"];
        }

        $foreignKey = is_null($foreignKey) ? $this->getForeignKey() : $foreignKey;
        $fullModelName = "\\App\\Models\\{$modelName}";

        if (class_exists($fullModelName)) {
            $collection = $fullModelName::findByFilter(
                array($this->getForeignKey(), '=', $this->get('id')),
                false,
                array('id' => 'ASC')
            );

            $collection->setParent($this);

            $this->relations["{$modelName}_{$foreignKey}_hasmany"] = $collection;
            return $this->relations["{$modelName}_{$foreignKey}_hasmany"];
        } else {
            throw new \InvalidArgumentException("Model {$fullModelName} does not exist!");
        }
    }

    // PRIVATE STATIC HELPERS
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

    public static function getTableName()
    {
        $self_class = get_called_class();
        if ($self_class === false) {
            throw new \UnexpectedValueException('Oh shit.');
        }
        $self_class = strtolower($self_class);

        return preg_replace('/(.+)\\\/', '', $self_class).'s';
    }

    public static function getModelName()
    {
        return rtrim(self::getTableName(), 's');
    }

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

    protected static function getModelData(array $filters, $limit = false, $order = array('id' => 'DESC'))
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
}
