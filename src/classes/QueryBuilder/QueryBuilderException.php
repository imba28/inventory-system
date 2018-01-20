<?php
namespace App\QueryBuilder;

class QueryBuilderException extends \Exception {
    private $data;

    public function __construct($message, $data = array()) {
        $this->data = $data;
        parent::__construct($message);
    }

    public function getData() {
        return $this->data;
    }
}

?>