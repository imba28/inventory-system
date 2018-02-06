<?php
namespace App\Models;

class Action extends \App\Model
{
    protected $id;
    protected $product;
    protected $customer;
    protected $rentDate;
    protected $returnDate = null;
    protected $expectedReturnDate = null;

    public function __construct($data = array())
    {
        parent::__construct($data);

        $this->on('set', function ($e) {
 // convert datetime fields to Y-m-d H:i:s
            $property = ($e->getInfo())['property'];
            $value = $e->getInfo()['value'];
            if ($property === 'returnDate' || $property === 'expectedReturnDate') {
                if ($value === 'NOW()') {
                    return;
                }
                $date = tryParseDate($value);
                if (!is_null($date)) {
                    $this->data[$property] = $date->format('Y-m-d H:i:s');
                }
            }
        });
    }

    public function isProductReturned()
    {
        return !is_null($this->returnDate);
    }

    public function returnProduct($date = 'NOW()')
    {
        $this->set('returnDate', $date);
        $this->save();
    }
}
