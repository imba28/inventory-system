<?php
namespace App\Helper\Loggers;

use App\Models\Log;

class DBLogger implements LoggerInterface
{
    public function log($logLevel, $message): bool
    {
        $logObj = Log::new();

        $logObj->set('message', $message);
        $logObj->set('type', $logLevel);

        return $logObj->save();
    }

    public function clean(): bool
    {
        return Log::removeAll();
    }

    public static function setPDO(PDO $handle)
    {
        static::$pdo = $handle;
    }
}
