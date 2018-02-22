<?php
namespace App\Models;

class Action extends \App\Model
{
    protected $attributes = ['product', 'customer', 'rentDate', 'returnDate', 'expectedReturnDate'];

    protected function init()
    {
        $this->on(
            'set',
            function ($e) {
                $property = $e->getInfo()['property'];
                $value = $e->getInfo()['value'];

                if (in_array($property, ['returnDate', 'expectedReturnDate'])) {
                    $date = tryParseDate($value);
                    if (!is_null($date)) {
                        $this->data[$property] = $date->format('Y-m-d H:i:s');
                    }
                }
            }
        );
    }

    public function isProductReturned()
    {
        return !is_null($this->get('returnDate'));
    }

    public function returnProduct($date = 'NOW()')
    {
        $this->set('returnDate', $date);
        $this->save();
    }
}
