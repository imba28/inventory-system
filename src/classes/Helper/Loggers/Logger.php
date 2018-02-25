<?php
namespace App\Helper\Loggers;

class Logger
{
    private static $logger;

    public static function info($msg)
    {
        return static::log('info', $msg);
    }

    public static function debug($msg)
    {
        return static::log('debug', $msg);
    }

    public static function warn($msg)
    {
        return static::log('warning', $msg);
    }

    public static function error($msg)
    {
        return static::log('error', $msg);
    }

    public static function fatal($msg)
    {
        return static::log('fatal', $msg);
    }

    protected static function log($logLevel, $msg)
    {
        return self::$logger->log($logLevel, $msg);
    }

    public static function clean()
    {
        return self::$logger->clean();
    }

    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }
}
