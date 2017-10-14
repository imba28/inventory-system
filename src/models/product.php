<?php
namespace App\Models;

class Product extends \App\Model {
    protected $name;
    protected $invNr;
    protected $type;
    protected $description;
    protected $condition;
    protected $note;

    public function isAvailable() {
        try {
            $action = \App\Models\Action::grabByFilter(array(
                array('product_id', '=', $this->id),
                array('returnDate', 'IS', 'NULL')
            ));
            return count($action) == 0;
        }
        catch(\InvalidArgumentException $e) {
            return true;
        }
    }
}
?>