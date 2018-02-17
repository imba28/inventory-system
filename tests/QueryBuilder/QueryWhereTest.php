<?php
use PHPUnit\Framework\TestCase;
use App\QueryBuilder\QueryWhere;

class QueryWhereTest extends TestCase
{
    public function testConstructor()
    {
        try {
            $where = new QueryWhere('a', '=', 1, 'test');
            $this->assertTrue(true);            
        } catch(Exception $e) {
            $this->assertFalse(true);
        }

        try {
            $where = new QueryWhere('a', 'IS', 1, 'test');
            $this->assertTrue(true);            
        } catch(Exception $e) {
            $this->assertFalse(true);
        }

        try {
            $where = new QueryWhere('a', '<>', 1, 'test');
            $this->assertTrue(true);            
        } catch(Exception $e) {
            $this->assertFalse(true);
        }

        try {
            $where = new QueryWhere('a', '1', 1, 'test');
            $this->assertFalse(true);            
        } catch(Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetClause()
    {
        $where = new QueryWhere("a", "=", 5, 'test');

        $this->assertEquals($where->getClause(), "(`test`.`a` = ?)");
        $this->assertEquals($where->getBindings(), array(5));

        $where = new QueryWhere("name", "<>", "santa", 'christmas');

        $this->assertEquals($where->getClause(), "(`christmas`.`name` <> ?)");
        $this->assertEquals($where->getBindings(), array("santa"));

        $where = new QueryWhere(
            array(
                "id",
                ">",
                10
            ),
            "OR",
            array(
                "id",
                "=",
                42
            ),
            'test'
        );

        $this->assertEquals($where->getClause(), "((`test`.`id` > ?) OR (`test`.`id` = ?))");
        $this->assertEquals($where->getBindings(), array(10, 42));

        $where = new QueryWhere(
            array(
                array(
                    "id",
                    "IS NOT",
                    "NULL"
                ),
                "AND",
                array(
                    "id",
                    ">",
                    10
                ),
            ),
            "OR",
            array(
                "id",
                "=",
                42
            ),
            'test'
        );

        $this->assertEquals($where->getClause(), "(((`test`.`id` IS NOT NULL) AND (`test`.`id` > ?)) OR (`test`.`id` = ?))");
        $this->assertEquals($where->getBindings(), array(10, 42));
    }

    public function testToString()
    {
        $where = new QueryWhere("a", "=", 5, 'test');
        $this->assertEquals((string)$where, $where->getClause());
    }
}
