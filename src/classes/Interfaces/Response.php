<?php
namespace App\Interfaces;

interface Response
{
    public function setStatus($status);
    public function addHeader($key, $value);
    public function append($data);
    public function flush();
}
