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

    public function testFilter()
    {
        $model_1 = new TestModel(array('id' => 1, 'department' => 'A'));
        $model_2 = new TestModel(array('id' => 2, 'department' => 'A'));
        $model_3 = new TestModel(array('id' => 3, 'department' => 'a'));
        $model_4 = new TestModel(array('id' => 4, 'department' => 'B'));
        $model_5 = new TestModel(array('id' => 5, 'department' => 'B'));
        $model_6 = new TestModel(array('id' => 6, 'department' => 'C'));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);
        $this->collection->append($model_4);
        $this->collection->append($model_5);
        $this->collection->append($model_6);

        $filtered = $this->collection->filter(function ($model) {
            return $model->getId() > 3;
        });

        $this->assertCount(3, $filtered);
        $this->assertContains($model_4, $filtered);
        $this->assertContains($model_5, $filtered);
        $this->assertContains($model_6, $filtered);
    }

    public function testWhere()
    {
        $model_1 = new TestModel(array('id' => 1, 'department' => 'A'));
        $model_2 = new TestModel(array('id' => 2, 'department' => 'B'));
        $model_3 = new TestModel(array('id' => 3, 'department' => 'B'));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);

        $filtered = $this->collection->where('department', '=', 'B');

        $this->assertCount(2, $filtered);
        $this->assertContains($model_2, $filtered);
        $this->assertContains($model_3, $filtered);
    }

    public function testMap()
    {
        $model_1 = new TestModel(array('id' => 1, 'department' => 'A'));
        $model_2 = new TestModel(array('id' => 2, 'department' => 'B'));
        $model_3 = new TestModel(array('id' => 3, 'department' => 'C'));
        $model_4 = new TestModel(array('id' => 4, 'department' => 'C'));

        $this->collection->append($model_1);
        $this->collection->append($model_2);
        $this->collection->append($model_3);
        $this->collection->append($model_4);

        $collection = $this->collection->map(function ($model) {
            return $model->get('department');
        });

        $this->assertCount(4, $collection);
        $this->assertEquals(['A', 'B', 'C', 'C'], $collection->toArray());
    }
}

class TestModel extends Model
{
    protected $attributes = ['department'];
}
