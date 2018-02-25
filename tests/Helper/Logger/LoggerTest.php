<?php
use PHPUnit\Framework\TestCase;
use App\Helper\Loggers\Logger;
use App\Helper\Loggers\LoggerInterface;

class LoggerTest extends TestCase
{
    public function testLog()
    {
        $stub = $this->getMockBuilder(LoggerInterface::class)
        ->setMethods(['clean', 'log'])
        ->getMock();

        $stub->method('log')->will($this->returnCallback('callback'));
        $stub->method('clean')->willReturn('log cleaned!');

        Logger::setLogger($stub);
        
        $this->assertEquals(Logger::info('log!'), 'info: log!');
        $this->assertEquals(Logger::debug('log!'), 'debug: log!');
        $this->assertEquals(Logger::warn('log!'), 'warn: log!');
        $this->assertEquals(Logger::error('log!'), 'error: log!');
        $this->assertEquals(Logger::fatal('log!'), 'fatal: log!');
    }
}

function callback($log, $msg)
{
    return "{$log}: {$msg}";
}
