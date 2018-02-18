<?php
use PHPUnit\Framework\TestCase;
use App\QueryBuilder\Builder;
use App\Model;

class ModelTest extends TestCase
{
    public function setUp()
    {
        $stub = $this->getMockBuilder(Builder::class)
        ->setConstructorArgs(['customers'])
        ->setMethods(['update', 'insert'])
        ->getMock();

        $stub->method('insert')->willReturn(true);
        $stub->method('update')->willReturn(true);

        Model::setQueryBuilder($stub);
    }

    public function testModelCreation()
    {
        $this->assertInstanceOf(
            \App\Models\Product::class,
            \App\Models\Product::new()
        );

        $customer = new \App\Models\Customer($this->existingModelProvider());
        $this->assertEquals(1, $customer->getId());
        $this->assertEquals('Test Customer', $customer->get('name'));
        $this->assertEquals('test@test.de', $customer->get('email'));
        $this->assertTrue($customer->isCreated());
    }

    public function testIfModelCanSetProperties()
    {
        $customer = new \App\Models\Customer($this->existingModelProvider());

        $this->assertFalse($customer->set('notExistingProperty', 'test'));
        $this->assertNull($customer->get('notExistingProperty'));

        $this->assertTrue($customer->set('name', 'Test Customer modified'));
        $this->assertEquals('Test Customer modified', $customer->get('name'));
    }

    public function testExceptionIfNothingChanged()
    {
        $customer = new \App\Models\Customer($this->existingModelProvider());

        $this->expectException(\App\QueryBuilder\NothingChangedException::class);
        $customer->save(true);
    }

    public function testGetChangedProperties()
    {
        $customer = new \App\Models\Customer($this->existingModelProvider());

        $this->assertEmpty($customer->getChangedProperties());
        $this->assertTrue($customer->set('name', 'Changed'));
        $this->assertNotEmpty($customer->getChangedProperties());
        $this->assertEquals($customer->getChangedProperties(), array('name' => 'Changed'));

        $customer->save();
        $this->assertEmpty($customer->getChangedProperties());
    }


    private function existingModelProvider()
    {
        return array(
            'id' => 1,
            'name' => 'Test Customer',
            'email' => 'test@test.de'
        );
    }
}
