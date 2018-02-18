<?php
use PHPUnit\Framework\TestCase;

use App\File\Image;

class FileTest extends TestCase
{
    public function testValidation()
    {
        $image = new Image($this->validFileProvider());
        $this->assertTrue($image->isValid());

        $image = new Image($this->invalidFileProvider());
        $this->assertFalse($image->isValid());

        $image = new Image($this->invalidFileProvider2());
        $this->assertFalse($image->isValid());
    }

    public function testGetSource()
    {
        $image = new Image($this->validFileProvider());
        $this->assertEquals($image->getSource(), 'tests/test.png');

        $image = new Image($this->invalidFileProvider());
        $this->assertEquals($image->getSource(), 'tests/test.txt');
    }
    
    protected function validFileProvider(): array
    {
        return array(
            'name' => 'test.png',
            'type' => 'image/png',
            'tmp_name' => 'tests/test.png',
            'error' => 0,
            'size' => filesize('tests/test.txt')
        );
    }

    protected function invalidFileProvider(): array
    {
        return array(
            'name' => 'test.txt',
            'type' => 'text/plain',
            'tmp_name' => 'tests/test.txt',
            'error' => 0,
            'size' => filesize('tests/test.txt')
        );
    }

    protected function invalidFileProvider2(): array
    {
        return array(
            'name' => 'test.png',
            'type' => 'text/plain',
            'tmp_name' => 'tests/test.png',
            'error' => 1,
            'size' => filesize('tests/test.png')
        );
    }
}
