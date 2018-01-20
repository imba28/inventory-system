<?php
namespace App\Traits;

trait GetSetData {
    protected $data = array();

    public function set($property, $value) {
        if(property_exists($this, $property)) {
            $this->data[$property] = $value;
            $this->trigger('set', $this, array('property' => $property, 'value' => $value));
        }
        return false;
    }

    public function get($property) {
        if(property_exists($this, $property)) {
            return $this->data[$property];
        }
        return null;
    }
}
?>