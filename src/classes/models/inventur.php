<?php
namespace App\Models;

class Inventur extends \App\Model {
    protected $startDate;
    protected $finishDate;
    protected $user;

    public function isStarted() {
        return !is_null($this->startDate);
    }
}