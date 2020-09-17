<?php
use PHPUnit\Framework\TestCase;

use App\File\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;

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
        $this->assertEquals($image->getSource(), __DIR__ . '/test.png');

        $image = new Image($this->invalidFileProvider());
        $this->assertEquals($image->getSource(), __DIR__. '/test.txt');
    }
    
    protected function validFileProvider(): UploadedFile
    {
        return new UploadedFile('tests/test.png', 'test.png', 'image/png', 0);
    }

    protected function invalidFileProvider(): UploadedFile
    {
        return new UploadedFile('tests/test.txt', 'test.txt', 'text/plain', 0);
    }

    protected function invalidFileProvider2(): UploadedFile
    {
        return new UploadedFile('tests/test.png', 'test.png', 'image/png', 1);
    }
}
