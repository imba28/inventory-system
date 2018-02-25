<?php
use PHPUnit\Framework\TestCase;
use App\Helper\Loggers\Logger;
use App\Helper\Loggers\FileLogger;

class FileLoggerTest extends TestCase
{
    public function testLogCreation()
    {
        Logger::info('a note!');
        $this->assertFileExists($this->logFile);
    }

    public function testLogContent()
    {
        Logger::info('a note!');
        $expected = date(FileLogger::getLogDateFormat()) . ' [info]: a note!';
        $this->assertEquals($expected, trim(file_get_contents($this->logFile)));
    }

    public function testCleanFile()
    {
        Logger::warn("warning!");
        Logger::warn("warning!");
        $this->assertTrue(file_exists($this->logFile));

        Logger::clean();
        $this->assertFalse(file_exists($this->logFile));
    }

    public function setUp()
    {
        $this->logFile = getcwd() . '/logs/tests.txt';
        Logger::setLogger(new FileLogger($this->logFile));
    }

    public function tearDown()
    {
        Logger::clean();
    }
}
