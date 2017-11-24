<?php
namespace App\Models;

class InventurProduct extends \App\Model {
    protected $product;
    protected $inventur;
    protected $in_stock;

    public function isInStock() {
        return $this->in_stock;
    }
}
?>