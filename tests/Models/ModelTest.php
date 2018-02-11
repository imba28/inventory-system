<?php
use PHPUnit\Framework\TestCase;

class ModelTest extends TestCase
{
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


    private function existingModelProvider()
    {
        return array(
            'id' => 1,
            'name' => 'Test Customer',
            'email' => 'test@test.de'
        );
    }
}
