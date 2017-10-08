<?php
namespace App\Rental;

class Action extends \App\Model {
    protected $id;
    protected $product;
    protected $customer;
    protected $rentDate;
    protected $returnDate = null;

    public function __construct(\App\Models\Product $product, \App\Models\Customer $customer, $date = null) {
        $this->product = $product;
        $this->customer = $customer;
        $this->rentDate = is_null($date) ? time() : $date;
    }

    public function getProduct() {
        return $this->product;
    }
    public function getCustomer() {
        return $this->customer;
    }
    public function getRentDate() {
        return $this->rentDate;
    }
    public function getReturnDate() {
        return $this->returnDate;
    }

    public function isProductReturned() {
        return !is_null($this->returnDate);
    }

    public function returnProduct() {
        $this->returnDate = time();
        
        $this->save();
    }
}
?>