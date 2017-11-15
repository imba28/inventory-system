<?php
namespace App\Interfaces;

interface Request {
    public function issetParam($param);
    public function getParam($param);
    public function getParams();
    public function getHeader($name);

    public function issetFile($name);
    public function getFile($name);
}
?>