<?php
namespace App\Models;

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
}
