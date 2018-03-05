<?php
use PHPUnit\Framework\TestCase;
use App\Database\DataMapper;

class DataMapperTest extends TestCase
{
    protected function setUp()
    {
        $this->mapper = new DataMapper([
            [
                'Type' => 'varchar(55)',
                'Field'=> 'name'
            ],
            [
                'Type' => 'int(11)',
                'Field'=> 'active'
            ],
            [
                'Type' => 'datetime',
                'Field'=> 'created_at'
            ],
            [
                'Type' => 'timestamp',
                'Field'=> 'updated_at'
            ]
        ]);
    }

    public function testMapTo()
    {
        $column = 'name';
        $this->assertInternalType('string', $this->mapper->mapTo($column, 'John Doe'));

        $column = 'active';
        $this->assertInternalType('int', $this->mapper->mapTo($column, '0'));

        $column = 'created_at';
        $this->assertInstanceOf(\DateTime::class, $this->mapper->mapTo($column, '2018-03-04 22:52:00'));

        $column = 'created_at';
        $this->assertInstanceOf(\DateTime::class, $this->mapper->mapTo($column, 'now'));

        $column = 'updated_at';
        $this->assertInstanceOf(\DateTime::class, $this->mapper->mapTo($column, '1520214233'));
        
        
        $column = 'created_at';
        $this->assertInternalType('string', $this->mapper->mapTo($column, 'invalid timestamp'));
        
        $column = 'updated_at';
        $this->assertInternalType('string', $this->mapper->mapTo($column, 'invalid datetime'));
    }

    public function testMapToAll()
    {
        $data = [
            'name' => 'John Doe',
            'active' => '1',
            'created_at' => '2018-03-04 22:52:00',
            'updated_at' => '1520214233'
        ];

        $mappedData = $this->mapper->mapToAll($data);

        $this->assertInternalType('string', $mappedData['name']);
        $this->assertInternalType('int', $mappedData['active']);
        $this->assertInstanceOf(\DateTime::class, $mappedData['created_at']);
        $this->assertInstanceOf(\DateTime::class, $mappedData['updated_at']);
    }
}
