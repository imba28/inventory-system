<?php
namespace App\Helper\Loggers;

class FileLogger implements LoggerInterface
{
    private $logFile;
    private static $format = "DATE [LOGLEVEL]: MESSAGE\n";
    private static $dateFormat = 'Y-m-d, H:i:s A';

    public function __construct(string $logFile)
    {
        $this->logFile = $logFile;
    }

    public static function setDateFormat(string $format)
    {
        self::$dateFormat = $format;
    }

    public static function getLogDateFormat()
    {
        return self::$dateFormat;
    }

    public static function getLogFormat()
    {
        return self::$format;
    }

    public function log($logLevel, $msg)
    {
        $logMessage = str_replace(array('DATE', 'LOGLEVEL', 'MESSAGE'), array(date(self::$dateFormat), $logLevel, $msg), self::$format);

        $handle = fopen($this->logFile, 'a');

        if ($handle) {
            fwrite($handle, $logMessage);
            fclose($handle);
        }
    }

    public function clean()
    {
        if (file_exists($this->logFile)) {
            unlink($this->logFile);
        }
    }
}
