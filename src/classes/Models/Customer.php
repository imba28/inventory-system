<?php
namespace App\Models;

class Customer extends Model
{
    protected $attributes = ['name', 'internal_id', 'email', 'phone'];
    protected $validators = ['internal_id' => 'required', 'email' => 'email'];
}
