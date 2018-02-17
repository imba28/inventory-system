<?php
use PHPUnit\Framework\TestCase;

class HttpResponseTest extends TestCase
{
    protected $response;

    protected function setUp()
    {
        $this->response = new \App\HttpResponse();
    }

    public function testSetStatus() {
        $this->assertEquals('200 OK', $this->response->getStatus());        

        $this->response->setStatus('404 Not Found');
        $this->assertEquals('404 Not Found', $this->response->getStatus());
        
        $this->response->setStatus(404);
        $this->assertEquals('404', $this->response->getStatus());
    }

    public function testAddHeader()
    {
        $this->response->addHeader('Location', 'https://google.de');
        $this->assertArrayHasKey('Location', $this->response->getHeaders());        
        $this->assertContains('https://google.de', $this->response->getHeaders());
    

        $this->response->addHeader('Authorization', 'somerandomhash');
        $this->assertArrayHasKey('Authorization', $this->response->getHeaders());
        $this->assertContains('somerandomhash', $this->response->getHeaders());  
    }
    
    public function testAppend()
    {
        $this->response->append('Hello');
        $this->response->append('World');
        $this->response->append('!');    
        
        $this->assertEquals($this->response->getBody(), 'HelloWorld!');
    }

    public function testFlush()
    {
        $this->response->append('Hello World');

        ob_start();
        $this->response->flush();
        $response = ob_get_clean();
        
        $this->assertTrue(strlen($response) === 11);
        $this->assertEquals($response, 'Hello World');
        $this->assertNull($this->response->getBody());
        $this->assertEmpty($this->response->getHeaders());
    }
}
