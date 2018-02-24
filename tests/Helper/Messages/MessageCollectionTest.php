<?php
use PHPUnit\Framework\TestCase;
use App\Helper\Messages\MessageCollection;

class MessageCollectionTest extends TestCase
{
    protected function setUp()
    {
        $this->messages = new MessageCollection();
    }

    public function testAdd()
    {
        $this->messages->add('success', 'that worked!');

        $this->assertTrue($this->messages->has('success'));
        $this->assertFalse($this->messages->has('error'));

        $this->assertContains('that worked!', $this->messages->get('success'));
        $this->assertEquals(count($this->messages->get('success')), 1);
    }

    public function testMultipleAdd()
    {
        $this->messages->add('success', 'that worked!');
        $this->messages->add('success', 'that worked too!');
        $this->messages->add('error', 'that didn\'t work.');

        $this->assertTrue($this->messages->has('success'));
        $this->assertTrue($this->messages->has('error'));

        $this->assertCount(2, $this->messages->get('success'));
        $this->assertCount(1, $this->messages->get('error'));

        $this->assertCount(2, $this->messages->all());
    }

    public function testGet()
    {
        $this->messages->add('success', 'that worked!');
        
        $this->assertEquals($this->messages->get('success'), array('that worked!'));
    }

    public function testHasValue()
    {
        $this->messages->add('success', 'that worked!');
        $this->messages->add('success', 'that worked too!');

        $this->assertTrue($this->messages->hasValue('success', 'that worked!'));
        $this->assertTrue($this->messages->hasValue('success', 'that worked too!'));
        $this->assertFalse($this->messages->hasValue('success', 'foo'));

        $this->assertFalse($this->messages->hasValue('error', 'foobar'));
    }

    public function testAny()
    {
        $this->assertFalse($this->messages->any());
        $this->messages->add('success', 'cool!');
        $this->assertTrue($this->messages->any());
    }

    public function testMerge()
    {
        $other = new MessageCollection();
        $other->add('a', 'a1');
        $other->add('a', 'a2');
        $other->add('b', 'b1');

        $this->messages->add('c', 'c1');
        $this->messages->add('a', 'a1');
        $this->messages->add('a', 'a3');

        $this->messages->merge($other);

        $this->assertTrue($this->messages->has('a'));
        $this->assertTrue($this->messages->has('b'));
        $this->assertTrue($this->messages->has('c'));

        $this->assertTrue($this->messages->hasValue('a', 'a1'));
        $this->assertTrue($this->messages->hasValue('a', 'a2'));
        $this->assertTrue($this->messages->hasValue('a', 'a3'));
        $this->assertTrue($this->messages->hasValue('b', 'b1'));
        $this->assertTrue($this->messages->hasValue('c', 'c1'));


        $this->assertFalse($this->messages->has('d'));
        $this->assertFalse($this->messages->hasValue('d', 'd'));

        $this->assertCount(3, $this->messages->get('a'));
        $this->assertCount(1, $this->messages->get('b'));
        $this->assertCount(1, $this->messages->get('c'));

        $this->assertCount(3, $this->messages->all());
    }

    public function testRemove()
    {
        $this->messages->add('foo', '1');
        $this->messages->add('bar', '1');
        $this->assertTrue($this->messages->remove('foo'));
        $this->assertCount(1, $this->messages->all());

        $this->assertFalse($this->messages->remove('foobar'));
    }

    public function testRemoveValue()
    {
        $this->messages->add('foo', '1');
        $this->messages->add('bar', '1');
        $this->messages->add('bar', '2');

        $this->assertTrue($this->messages->removeValue('bar', '1'));
        $this->assertFalse($this->messages->removeValue('foo', '2'));
        $this->assertFalse($this->messages->removeValue('foo', null));
        $this->assertFalse($this->messages->removeValue(null, null));
        
        $this->assertCount(2, $this->messages->all());

        $this->assertTrue($this->messages->removeValue('bar', '2'));
        $this->assertCount(1, $this->messages->all());
    }
}
