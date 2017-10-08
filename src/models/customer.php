<?php
namespace App\Models;

class Customer extends \App\Model {
    protected $name;
    protected $fhs;
    protected $email;

    public function __construct($id) {
        $this->id = $id;
    }
}
?>