<?php
namespace App\Models;

class Product extends \App\Model {
    protected $name;
    protected $type;
    protected $condition;
    protected $note;

    public function __construct($id) {
        $this->id = $id;
    }
}
?>