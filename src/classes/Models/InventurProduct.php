<?php
namespace App\Models;

class InventurProduct extends \App\Model
{
    protected $product;
    protected $inventur;
    protected $in_stock;
    protected $missing;

    public function isInStock()
    {
        return $this->in_stock;
    }
}
