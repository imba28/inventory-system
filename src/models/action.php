<?php
namespace App\Models;

class Action extends \App\Model {
    protected $id;
    protected $product;
    protected $customer;
    protected $rentDate = 'NOW()';
    protected $returnDate = null;
    protected $expectedReturnDate = null;

    public function isProductReturned() {
        return !is_null($this->returnDate);
    }

    public function returnProduct() {
        $this->returnDate = 'NOW()';
        $this->save();
    }
}
?>