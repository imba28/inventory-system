<?php
namespace App\Models;

class Action extends Model
{
    protected $attributes = ['product', 'customer', 'rentDate', 'returnDate', 'expectedReturnDate'];

    public function isProductReturned()
    {
        return !is_null($this->get('returnDate'));
    }

    public function returnProduct($date = 'NOW')
    {
        $this->set('returnDate', $date);
        return $this->save();
    }
}
