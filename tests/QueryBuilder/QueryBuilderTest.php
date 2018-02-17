<?php
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    protected $query;

    protected function setUp()
    {
        $this->query = new \App\QueryBuilder\Builder('test');
    }

    public function testQueryBuilderSQL()
    {
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test`');
    }

    public function testSimpleSelection()
    {
        $this->query->select(array('a', 'b', 'c'));
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`a`, `test`.`b`, `test`.`c` FROM `test`');
    }

    public function testMultiSelection()
    {
        $this->query->select(array('a', 'b', 'c'));
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`a`, `test`.`b`, `test`.`c` FROM `test`');
    }

    public function testSimpleWhere()
    {
        $this->query->where('id', '=', '1');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` WHERE (`test`.`id` = ?)');
    }

    public function testMultiWhere()
    {
        $this->query->where(array(
            array('id', '=', '1'),
            array('id', '=', '2')
        ));
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` WHERE (`test`.`id` = ?) AND (`test`.`id` = ?)');
    }

    public function testMultiWhereWithOperator()
    {
        $this->query->where(
            array('id', '=', '1'),
            'OR',
            array('id', '=', '2')
        );
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` WHERE ((`test`.`id` = ?) OR (`test`.`id` = ?))');
    }

    public function testInvalidMultiWhere()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->query->where(
            array('id', '=', '1'),
            'OR'
        );
    }

    public function testNext()
    {
        $this->query->next(10);
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` WHERE (`test`.`id` > ?) ORDER BY `test`.`id` ASC LIMIT 1');
    }

    public function testNextWithSelection()
    {
        $this->query->next(10, array('id', 'name'));
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`id`, `test`.`name` FROM `test` WHERE (`test`.`id` > ?) ORDER BY `test`.`id` ASC LIMIT 1');
    }

    public function testPrev()
    {
        $this->query->prev(10);
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` WHERE (`test`.`id` < ?) ORDER BY `test`.`id` DESC LIMIT 1');
    }

    public function testPrevWithSelection()
    {
        $this->query->prev(10, array('id', 'name'));
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`id`, `test`.`name` FROM `test` WHERE (`test`.`id` < ?) ORDER BY `test`.`id` DESC LIMIT 1');
    }

    public function testOrderBy()
    {
        $this->query->orderBy('id');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` ORDER BY `test`.`id` DESC');

        $this->query->orderBy('name', 'ASC');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` ORDER BY `test`.`id` DESC, `test`.`name` ASC');

        $this->query->orderBy('sort', 'DESC');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test` ORDER BY `test`.`id` DESC, `test`.`name` ASC, `test`.`sort` DESC');
    }

    public function testGroupBy()
    {
        $this->query->select('COUNT(*)')->groupBy('category');
        $this->assertEquals($this->query->getSql(), 'SELECT COUNT(*) FROM `test` GROUP BY `test`.`category`');
    }

    public function testJoinTable()
    {
        $this->query->select('name')->join('table_2', 'id', '=', 'foreign_key')->select('status');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`name`, `table_2`.`status` FROM `test` LEFT JOIN `table_2` ON `test`.`id` = `table_2`.`foreign_key`');
    }

    public function testRightJoinTable()
    {
        $this->query->select('name')->join('table_2', 'id', '=', 'foreign_key', 'RIGHT')->select('status');
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.`name`, `table_2`.`status` FROM `test` RIGHT JOIN `table_2` ON `test`.`id` = `table_2`.`foreign_key`');
    }

    public function testSetTableName()
    {
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test`');

        $this->query->setTable('other_table');
        $this->assertEquals($this->query->getSql(), 'SELECT `other_table`.* FROM `other_table`');
    }

    public function testReset()
    {
        $this->query->select('name')->where('name', '=', '1')->orderBy('name')->groupBy('category')->limit(10)->join('table_2', 'id', '=', 'foreign_key', 'RIGHT')->select('status');

        $this->query->reset();
        $this->assertEquals($this->query->getSql(), 'SELECT `test`.* FROM `test`');
    }

    public function testSanitize()
    {
        $this->assertEquals($this->query->sanitize('name'), '`name`');
        $this->assertEquals($this->query->sanitizeColumnName('name'), '`test`.`name`');
    }

    public function testAlias()
    {
        $alias = $this->query::alias('name', 'firstname');
        $this->assertInstanceOf(\App\QueryBuilder\QueryAlias::class, $alias);
        $this->assertNotEmpty($alias->get('name'));
        $this->assertNotEmpty($alias->get('alias'));
        $this->assertEquals($alias->get('name'), 'name');
        $this->assertEquals($alias->get('alias'), 'firstname');
    }

    public function testRaw()
    {
        $raw = $this->query::raw('SUM(price)');
        $this->assertInstanceOf(\App\QueryBuilder\Raw::class, $raw);

        $this->query->select($this->query::alias($raw, 'total'));
        $this->assertEquals($this->query->getSql(), 'SELECT SUM(price) as total FROM `test`');
    }

    public function testFullSQL()
    {
        $this->query->where('name', '=', 'Wick')->where('firstname', '=', 'John');
        $this->assertEquals(
            $this->query->getFullSQL(),
            'SELECT `test`.* FROM `test` WHERE (`test`.`name` = \'Wick\') AND (`test`.`firstname` = \'John\')'
        );
    }

    public function testSetTablePrefix()
    {
        $this->query::setTablePrefix('pre');
        $this->assertEquals($this->query->getSql(), 'SELECT `pre_test`.* FROM `pre_test`');

        $this->query->select('name')->where('id', '=', 1);
        $this->assertEquals($this->query->getSql(), 'SELECT `pre_test`.`name` FROM `pre_test` WHERE (`pre_test`.`id` = ?)');
    }
}
