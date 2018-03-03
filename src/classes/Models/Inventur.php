<?php
namespace App\Models;

use App\Collection;

class Inventur extends Model
{
    protected $attributes = ['startDate', 'finishDate'];

    public function isStarted()
    {
        return !is_null($this->get('startDate'));
    }

    public function isFinished()
    {
        return !is_null($this->get('finishDate'));
    }

    public function items(): Collection
    {
        return $this->hasMany('InventurProduct');
    }

    public function itemsMissing(): Collection
    {
        return $this->items()->where('missing', '=', '1')->map(function ($item) {
            return $item->get('product');
        });
    }

    public function itemsScanned(): Collection
    {
        return $this->items()->where('in_stock', '=', '1')->map(function ($item) {
            return $item->get('product');
        });
    }

    public function itemsNotScanned(): Collection
    {
        return $this->items()->where('in_stock', '=', '0')->map(function ($item) {
            return $item->get('product');
        });
    }
}
