<?php
namespace App\Models;

class InventurProduct extends Model
{
    protected $attributes = ['product', 'inventur', 'in_stock', 'missing'];
    
    public function isInStock()
    {
        return $this->get('in_stock');
    }
}
