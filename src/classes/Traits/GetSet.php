<?php
namespace App\Traits;

trait GetSet
{
    public function set($property, $value)
    {
        if (property_exists($this, $property)) {
            $this->$property = $value;
        }
    }

    public function get($property)
    {
        if (property_exists($this, $property)) {
            return $this->$property;
        }
        return null;
    }
}
