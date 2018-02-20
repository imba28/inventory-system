<?php
namespace App\Database;

use \PDO;

interface DatabaseInterface
{
    public function getHandler(): PDO;
}
