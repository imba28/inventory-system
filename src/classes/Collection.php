<?php
namespace App;

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

    public function count()
    {
        return count($this->items);
    }

    public function create()
    {
        throw new \BadMethodCallException('not implemented yet');
    }

    public function isEmpty()
    {
        return count($this->items) == 0;
    }

    public function find($id)
    {
        foreach ($this->items as $item) {
            if ($item->getId() === $id) {
                return $item;
            }
        }
        return null;
    }

    public function first($n = 1)
    {
        if ($n > 1) {
            return array_slice($this->items, 0, $n);
        }
        return isset($this->items[$n - 1]) ? $this->items[$n - 1] : null;
    }

    public function append(\App\Model $model)
    {
        $this->items[] = $model;
    }

    public function toArray()
    {
        return $this->items;
    }

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

    /* SETTER */
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
