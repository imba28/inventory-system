<?php
use PHPUnit\Framework\TestCase;
use App\Collection;
use App\Models\Model;

class CollectionTest extends TestCase
{
    protected $collection;

    protected function setUp()
    {
        $this->collection = new Collection();
    }

    public function testCount()
    {
        $this->assertEquals($this->collection->count(), 0);

        $this->collection = new Collection(array('a', 'b', 'c'));

        $this->assertEquals($this->collection->count(), 3);
        $this->assertCount(3, $this->collection);
    }

    public function testIsEmpty()
    {
        $this->assertTrue($this->collection->isEmpty());
        $this->collection->append(new TestModel());
        $this->assertFalse($this->collection->isEmpty());
    }

    public function testFind()
    {
        $model_1 = new TestModel(array('id' => 1));
        $model_2 = new TestModel(array('id' => 2));
        $model_3 = new TestModel(array('id' => 3));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);

        $this->assertInstanceOf(Model::class, $this->collection->find(1));
        $this->assertEquals($this->collection->find(1), $model_1);
        $this->assertEquals($this->collection->find(2), $model_2);
        $this->assertEquals($this->collection->find(3), $model_3);
    }

    public function testFirst()
    {
        $model_1 = new TestModel(array('id' => 1));
        $model_2 = new TestModel(array('id' => 2));
        $model_3 = new TestModel(array('id' => 3));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);

        $this->assertInstanceOf(Model::class, $this->collection->first());
        $this->assertEquals($this->collection->first(), $model_1);

        $this->assertEquals($this->collection->first(1), $model_1);
        $this->assertNull($this->collection->first(-1));

        $this->assertEquals($this->collection->first(2), [$model_1, $model_2]);
        $this->assertEquals($this->collection->first(2), [$model_1, $model_2]);
        $this->assertEquals($this->collection->first(100), [$model_1, $model_2, $model_3]);
    }

    public function testAppend()
    {
        $model_1 = new TestModel(array('id' => 1));
        $model_2 = new TestModel(array('id' => 2));
        $model_3 = new TestModel(array('id' => 3));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);

        $this->assertEquals($this->collection->count(), 3);
    }

    public function testToArray()
    {
        $model_1 = new TestModel(array('id' => 1));
        $model_2 = new TestModel(array('id' => 2));
        $model_3 = new TestModel(array('id' => 3));

        $this->collection->append($model_1);
        $this->collection->append($model_2);

        $this->assertEquals($this->collection->toArray(), [$model_1, $model_2]);

        $this->collection->append($model_3);
        $this->assertEquals($this->collection->toArray(), [$model_1, $model_2, $model_3]);
    }
}

class TestModel extends Model
{

}
