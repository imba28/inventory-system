<?php
use PHPUnit\Framework\TestCase;

class FormatTest extends TestCase
{
    protected $format;

    protected function setUp()
    {
        $this->format = new \App\Format();
    }

    public function testFormatClosureAdd()
    {
        $this->format->html(function () {
        });
        $this->assertTrue(true);

        $this->format->xml(function () {
        });
        $this->assertTrue(true);

        $this->format->json(function () {
        });
        $this->assertTrue(true);

        $this->expectException(\App\Exceptions\InvalidOperationException::class);
        $this->format->yaml(function () {
        });
    }

    public function testInvalidArgument()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->format->html("hello there");
    }

    public function testClosureExecution()
    {
        $this->format->html(function () {
            return 'html';
        });
        $this->format->json(function () {
            return 'json';
        });
        $this->format->xml(function () {
            return 'xml';
        });

        $this->assertEquals($this->format->execute('html'), 'html');
        $this->assertEquals($this->format->execute('json'), 'json');
        $this->assertEquals($this->format->execute('xml'), 'xml');
    }
}
