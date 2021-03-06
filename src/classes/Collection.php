<?php
namespace App;

use App\Models\Model;
use \Closure;

/**
 * Collection of models. Provides various useful helper methods.
 */
class Collection implements \Iterator, \ArrayAccess, \JsonSerializable, \Countable
{
    protected $items;
    private $parent;
    private $position;

    public function __construct($items = array())
    {
        $this->position = 0;
        $this->parent = null;
        $this->items = $items;
    }

    /**
     * Counts models in collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    public function create()
    {
        throw new \BadMethodCallException('not implemented yet');
    }

    /**
     * Check if collection is empty.
     *
     * @return void
     */
    public function isEmpty(): bool
    {
        return count($this->items) == 0;
    }

    /**
     *  Filters items using a model property, an operator and a value
     *
     * @param string $column
     * @param string $operator
     * @param string $value
     * @return \App\Collection
     */
    public function where(string $column, string $operator, string $value): Collection
    {
        return $this->filter(function ($item) use ($column, $operator, $value) {
            $prop = $item->get($column);
            switch ($operator) {
                case '=':
                    return  $prop == $value;
                break;
                case '>':
                    return $prop > $value;
                break;
                case '<':
                    return $prop < $value;
                break;
                case '!=':
                    return $prop != $value;
                break;
            }
        });
    }

    /**
     * Filters items using a callback function
     *
     * @param Closure $filter
     * @return \App\Collection
     */
    public function filter(Closure $filter): Collection
    {
        return new Collection(array_values(array_filter($this->items, $filter)));
    }

    /**
     * Applies the callback to the items of this collection
     *
     * @param Closure $callback
     * @return \App\Collection
     */
    public function map(Closure $callback): Collection
    {
        return new Collection(array_map($callback, $this->items));
    }

    /**
     * Find a model with a specific id or return null
     *
     * @param mixed $id
     * @return mixed
     */
    public function find($id)
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Return the first model in collection or the first n models
     *
     * @param mixed $n
     * @return mixed
     */
    public function first($n = 1)
    {
        if ($n > 1) {
            return array_slice($this->items, 0, $n);
        }
        return isset($this->items[$n - 1]) ? $this->items[$n - 1] : null;
    }

    /**
     * Add model to collection
     *
     * @param Model $model
     * @return void
     */
    public function append(Model $model)
    {
        $this->items[] = $model;
    }

    /**
     * Returns items in collection
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * Save models in collection
     *
     * @todo   should work without parent too
     * @return void
     */
    public function save()
    {
        if ($this->parent == null) {
            throw new \LogicException('Collections needs a parent model in order to save!');
        }

        foreach ($this->items as $item) {
            $item->set($this->parent::getModelName(), $this->parent);
            $item->save();
        }
    }

    /**
     * Sort collection by a model property
     *
     * @param mixed $by
     * @param mixed $order
     * @return void
     */
    public function sort($by, $order = 'ASC'): Collection
    {
        $order = $order === 'ASC' ? -1 : 1;

        usort($this->items, function ($a, $b) use ($by, $order) {
            if ($a->get($by) === $b->get($by)) {
                return 0;
            }

            return $a->get($by) < $b->get($by) ? $order : -$order;
        });

        return $this;
    }

    /**
     * Set parent model of collection.
     *
     * @param Model $parent
     * @return void
     */
    public function setParent(Model $parent)
    {
        $this->parent = $parent;
    }

    /* ITERATOR METHODS */
    public function current()
    {
        return $this->items[$this->position];
    }

    public function key()
    {
        return $this->position;
    }

    public function next()
    {
        return ++$this->position;
    }

    public function rewind()
    {
        $this->position = 0;
    }

    public function valid()
    {
        return isset($this->items[$this->position]);
    }

    /* ARRAY ACCESS METHODS */
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->items[$offset];
    }

    public function offsetSet($offset, $value)
    {
        $this->items[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        array_splice($this->items, $offset, 1);
    }

    /* Json serializable  */
    public function jsonSerialize()
    {
        return $this->items;
    }
}
