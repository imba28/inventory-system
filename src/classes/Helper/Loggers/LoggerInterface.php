<?php
namespace App\Helper\Loggers;

interface LoggerInterface
{
    public function log($logLevel, $msg);
    public function clean();
}
