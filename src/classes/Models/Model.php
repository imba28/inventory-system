<?php
namespace App\Models;

use App\Validator;
use App\Collection;
use App\Database\DataMapper;
use App\Helper\Loggers\Logger;
use App\QueryBuilder\Builder;
use App\Helper\Messages\MessageInterface;
use App\Helper\Messages\MessageCollection;

/**
 * Object relational mapper
 */
abstract class Model implements \JsonSerializable, MessageInterface
{
    use \App\Traits\Events;

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model's state when it was created.
     *
     * @var array
     */
    protected $originalState = [];

    /**
     * The model's current state.
     *
     * @var array
     */
    protected $state = [];

    /**
     * Array of validations that have to be passes before the model can bse successfully saved.
     *
     * @var array
     */
    protected $validators = [];

    /**
     * Lists of attributes that failed validations.
     *
     * @var array
     */
    private $invalidFields = [];
    
    /**
     * Collection of messages, set when validating
     *
     * @see isValid()
     * @var \App\Helper\Messages\MessageCollection
     */
    private $messages = null;

    private $foreignKey;

    /**
     * The model's relationships. Contains collections of other models.
     *
     * @var array
     */
    private $relations = [];

    /**
     * Data mapper. Converts model data based on its database field type.
     *
     * @var App\Database\DataMapper
     */
    private $mapper;

    /**
     * QueryBuilder which is used to retrieve data from the database.
     *
     * @var \App\QueryBuilder\Builder
     */
    private static $builder;

    /**
     * Array of arrays of models. Each model object exists only once.
     *
     * @var array
     */
    static private $instances = [];

    public function __construct($options = array(), DataMapper $mapper = null)
    {
        $this->originalState = array_merge($this->attributes, ['id', 'user', 'createDate', 'stamp', 'deleted']);
        $this->originalState = array_map(
            function () {
                return null;
            },
            array_flip($this->originalState)
        );

        $this->mapper = $mapper;

        foreach ($options as $key => $value) {
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

    /**
     * Helper method that is called inside constructor. May be overridden/extended from sub classes.
     *
     * @return void
     */
    protected function init()
    {
    }

    /**
     * Returns whether or not a model instance exists inside the database.
     *
     * @return bool
     */
    public function isCreated(): bool
    {
        return !empty($this->getId());
    }

    /**
     * Returns the model's id, helper method.
     *
     * @return int
     */
    public function getId()
    {
        return $this->get('id');
    }

    public function getErrors()
    {
        return $this->invalidFields;
    }

    /**
     * deletes the model
     *
     * @return bool
     */
    public function remove(): bool
    {
        return self::delete($this->getId());
    }

    /**
     * Deletes all models.
     *
     * @return bool
     */
    public function removeAll(): bool
    {
        self::$builder->setTable(self::getTableName());
        return self::$builder->update([
            'deleted' => '1'
        ]);
    }

    /**
     * Saves changed attributes to the database.
     *
     * If no changes to the state were made and the argument is set to true, an exception will be thrown.
     *
     * @param  bool $exception
     * @throws \App\Models\InvalidModelDataException
     * @return bool
     */
    public function save($exception = false): bool
    {
        if (!$this->isValid()) {
            throw new \App\Models\InvalidModelDataException("no valid!");
        }
        
        $this->trigger('save');

        self::$builder->setTable(self::getTableName());
        $propertiesUpdate = $this->getChangedProperties();

        if ($this->isCreated()) {
            if (count($propertiesUpdate) > 0) {
                $update = $this->mapper->mapFromAll($propertiesUpdate);
                $res = self::$builder->where('id', '=', $this->getId())->update($update);
    
                if ($res === false) {
                    throw new \App\QueryBuilder\QueryBuilderException("could not update data", self::$builder->getError());
                }
            } else {
                if ($exception) {
                    throw new \App\QueryBuilder\NothingChangedException("nothing changed!");
                }
            }
        } else {
            $this->trigger('create');

            $propertiesUpdate['createDate'] = new \DateTime('now');
            $propertiesUpdate['deleted'] = '0';

            $insert = $this->mapper->mapFromAll($propertiesUpdate);
            $res = self::$builder->insert($insert);

            if ($res === false) {
                throw new \App\QueryBuilder\QueryBuilderException("could not insert data", self::$builder->getError());
            }

            $this->set('id', self::$builder->lastInsertId());

            self::$instances[get_called_class()][$this->getId()] = $this;
        }

        $this->originalState = $this->state;

        array_walk(
            $this->relations,
            function ($collection) {
                $collection->save();
            }
        );

        return true;
    }

    public function isValid(): bool
    {
        $validator = new Validator($this->validators, $this->state);

        $passes = $validator->passes();

        if (!$passes) {
            $validator->messages();
            $this->invalidFields = $validator->getErrors();
            $this->messages = $validator->messages();
        }

        return $passes;
    }

    public function messages(): MessageCollection
    {
        return $this->messages;
    }

    /**
     * Sets multiple arguments at once.
     *
     * @param  array $data
     * @return void
     */
    public function setAll(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    /**
     * Sets an attribute, if it exists.
     *
     * Triggers the internal event 'set'.
     *
     * @param  mixed $property
     * @param  mixed $value
     * @return bool
     */
    public function set($property, $value)
    {
        if (array_key_exists($property, $this->originalState)) {
            $this->state[$property] = $this->mapper->mapTo($property, $value);
            $this->trigger('set', $this, array('property' => $property, 'value' => $value));
            return true;
        }
        return false;
    }

    /**
     * Returns a specific attribute, if it exists. If no argument is given, all attributes are returned.
     *
     * @param  mixed $property
     * @return mixed
     */
    public function get($property = null)
    {
        if (is_null($property)) {
            return $this->state;
        }

        if (array_key_exists($property, $this->state)) {
            return $this->state[$property];
        }
        return null;
    }

    public function jsonSerialize(): array
    {
        $json = array();

        foreach ($this->state as $key => $value) {
            if ($key == 'deleted') {
                continue;
            }

            if ($this->state[$key] instanceof \App\Models\Model) {
                $value = $value->jsonSerialize();
            }

            $json[$key] = $value;
        }

        return $json;
    }

    /**
     * Returns the model's foreign key. If no foreign key was set, a default key is generated based on the model's name.
     *
     * @return string
     */
    public function getForeignKey(): string
    {
        if (!isset($this->foreignKey)) {
            $namespaceParts = explode('\\', get_called_class());
            $this->foreignKey = strtolower($namespaceParts[count($namespaceParts) - 1] . '_id');
        }
        return $this->foreignKey;
    }

    /**
     * Returns all attributes that have changed since the last save operation.
     *
     * Converts attributes of type model automatically to foreign key => id
     *
     * @return array
     */
    public function getChangedProperties(): array
    {
        $propertiesUpdate = array();

        foreach ($this->state as $name => $value) {
            if ($this->originalState[$name] != $value) { // has changed
                /*if ($value === "now") {
                    $date = new \DateTime();
                    $value = $date->format('Y-m-d H:i:s');
                    $this->state[$name] = $value;
                }*/

                $propertiesUpdate[$name] = $value;
            }
        }

        return $propertiesUpdate;
    }

    /**
     * Searches for a model that matches $column = $value. Throws exception if no record was found.
     *
     * Called class determines the database table were the lookup is conducted.
     *
     * @param  mixed $value
     * @param  mixed $column
     * @return \App\Models\Model
     */
    public static function find($value, $column = 'id'): Model
    {
        $selfClass = get_called_class();
        if ($column == 'id' && isset(self::$instances[$selfClass][$value])) {
            return self::$instances[$selfClass][$value];
        }

        $options = self::getModelData(
            array(
            array($column, '=', $value)
            ),
            1
        );

        return self::getModelFromOption($options);
    }

    public static function findOrCreate($value, $column): Model
    {
        try {
            return self::find($value, $column);
        } catch (\App\Exceptions\NothingFoundException $e) {
            $model = self::new();
            $model->set($column, $value);
            return $model;
        }
    }

    /**
     * Find all models that match specific criterias.
     *
     * @param  array $filters
     * @param  mixed $limit
     * @param  mixed $order
     * @return \App\Collection
     */
    public static function findByFilter(array $filters, $limit = false, $order = array('id' => 'DESC'))
    {
        if ($limit !== 1) {
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

    /**
     * Find all models
     *
     * @return void
     */
    public static function all(): \Traversable
    {
        $options = self::getModelData(array());
        $collection = new Collection();

        foreach ($options as $option) {
            $collection->append(self::getModelFromOption($option));
        }

        return $collection;
    }

    /**
     * Factory method creates a new instance.
     *
     * @return \App\Models\Model
     */
    public static function new(): Model
    {
        $selfClass = get_called_class();
        if ($selfClass == false) {
            throw new \RuntimeException('Oh shit.');
        }
        
        self::$builder->setTable(self::getTableName());
        $mapper = new DataMapper(self::$builder->describe());
            
        $model = new $selfClass([], $mapper);
        //$model->setQueryBuilder(new QueryBuilder\Builder(self::getTableName()));

        return $model;
    }

    /**
     * Creates a new model instance, sets attributes and saves it afterwards.
     *
     * @param  array $data
     * @return \App\Models\Model
     */
    public static function create(array $data): Model
    {
        $obj = self::new();
        $obj->setAll($data);
        $obj->save();
        return $obj;
    }

    /**
     * Deletes a model with a specific id.
     *
     * @param  int $id
     * @return bool
     */
    public static function delete(int $id): bool
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

    /**
     * Initializes a one to many relation between this and other models. Returns a collection of models.
     *
     * Accepts the class name of another model and optionally the foreign key.
     *
     * @param  mixed $modelName
     * @param  mixed $foreignKey
     * @return \App\Collection
     */
    protected function hasMany($modelName, $foreignKey = null): Collection
    {
        $foreignKey = is_null($foreignKey) ? $this->getForeignKey() : $foreignKey;
        $fullModelName = "\\App\\Models\\{$modelName}";
        
        $trace = debug_backtrace();
        $relationName = $trace[1]['function'];

        if (!isset($this->relations[$relationName])) {
            if (class_exists($fullModelName)) {
                $collection = $this->isCreated() ?
                    $fullModelName::findByFilter(
                        array($this->getForeignKey(), '=', $this->get('id')),
                        false,
                        array('id' => 'ASC')
                    )
                    : new Collection();

                $collection->setParent($this);

                $this->relations[$relationName] = $collection;
            } else {
                throw new \InvalidArgumentException("Model {$fullModelName} does not exist!");
            }
        }

        return $this->relations[$relationName];
    }

    public static function where($key, $operator = null, $value = null): Collection
    {
        if (count(func_get_args()) == 2) {
            $value = $operator;
            $operator = '=';
        }
        
        if (count(func_get_args()) > 1) {
            $filter = [$key, $operator, $value];
        } else {
            $filter = $key;
        }

        return static::findByFilter($filter, false);
    }

    private static function getModelFromOption(array $data)
    {
        if (!isset(self::$instances[get_called_class()])) {
            self::$instances[get_called_class()] = array();
        }

        $selfClass = get_called_class();

        if (!isset(self::$instances[$selfClass][$data['id']])) {
            self::$builder->setTable(self::getTableName());
            $mapper = new DataMapper(self::$builder->describe());
            $data = $mapper->mapToAll($data);
            
            $model = new $selfClass($data, $mapper);
            //$model->setQueryBuilder(new QueryBuilder\Builder(self::getTableName()));

            self::$instances[$selfClass][$data['id']] = $model;
        }

        return self::$instances[$selfClass][$data['id']];
    }

    /**
     * Returns a model's table name. Table is assumed to be the plural of the class name.
     *
     * @return string
     */
    public static function getTableName(): string
    {
        $selfClass = get_called_class();
        if ($selfClass === false) {
            throw new \UnexpectedValueException('Oh shit.');
        }
        $selfClass = strtolower($selfClass);

        return preg_replace('/(.+)\\\/', '', $selfClass).'s';
    }

    public static function getModelName(): string
    {
        return rtrim(self::getTableName(), 's');
    }

    /**
     * Injects a query builder object.
     *
     * @param  \App\QueryBuilder\Builder $builder
     * @return void
     */
    public static function setQueryBuilder(Builder $builder)
    {
        self::$builder = $builder;
    }

    public static function getQuery(array $filters, $limit = false): \App\QueryBuilder\Builder
    {
        $query = new \App\QueryBuilder\Builder(self::getTableName());
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

    /**
     * Loads rows matching filters from the database.
     *
     * @param  array $filters
     * @param  mixed $limit
     * @param  mixed $order
     * @return mixed
     */
    protected static function getModelData(array $filters, $limit = false, $order = array('id' => 'DESC'))
    {
        //$query = new \App\QueryBuilder\Builder(self::getTableName());
        self::$builder->setTable(self::getTableName());
        self::$builder->where('deleted', '=', '0');

        if ($limit !== false) {
            self::$builder->limit($limit);
        }

        foreach ($order as $column => $order) {
            self::$builder->orderBy($column, $order);
        }

        if (count($filters) == 3 && is_string($filters[1])) {
            $filters = array($filters);
        }

        foreach ($filters as $filter) {
            if (count($filter) == 3) {
                self::$builder->where($filter[0], $filter[1], $filter[2]);
            }
        }

        $res = self::$builder->get();
        
        if (!empty($res)) {
            return $limit == 1 ? current($res) : $res;
        } else {
            throw new \App\Exceptions\NothingFoundException('No entries found for '. get_called_class() . '!');
        }
    }
}
