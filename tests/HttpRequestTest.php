<?php
use PHPUnit\Framework\TestCase;

class HttpRequestTest extends TestCase
{
    protected $request;

    protected function setUp()
    {
        $_REQUEST = array(
            'a' => 'A',
            'b' => 'B',
            'c' => 'C',
            '1' => 'foo',
            '2' => 'bar'
        );

        $this->request = new \App\HttpRequest();
    }

    public function testIssetParam()
    {
        $this->assertTrue($this->request->issetParam('a'));
        $this->assertTrue($this->request->issetParam('b'));
        $this->assertTrue($this->request->issetParam('1'));

        $this->assertFalse($this->request->issetParam('bar'));
        $this->assertFalse($this->request->issetParam('foo'));
    }

    public function testGetParam()
    {
        $this->assertEquals($this->request->getParam('a'), 'A');
        $this->assertEquals($this->request->getParam('b'), 'B');
        $this->assertEquals($this->request->getParam('2'), 'bar');

        $this->assertEquals($this->request->get('a'), 'A');
        $this->assertEquals($this->request->get('b'), 'B');
        $this->assertEquals($this->request->get('2'), 'bar');

        $this->assertNull($this->request->getParam('foo'));
        $this->assertNull($this->request->getParam('bar'));
    }

    public function testGetParams()
    {
        $this->assertEquals($this->request->getParams(), $_REQUEST);
    }
}
