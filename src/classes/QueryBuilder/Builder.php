<?php
namespace App\QueryBuilder;

class Builder
{
    protected $table;

    const SANITIZER = "`";

    protected $debug;
    protected $logging = true;

    protected $joins = array();
    protected $where = array();
    protected $selection = array();
    protected $group = array();
    protected $order = array();
    protected $limit = array();

    protected $result;
    protected $lastInsertID;
    protected $last_exception;

    private static $tablePrefix = null;

    protected static $sqlKeywords = array("NOW()", "COUNT(*)", "AUTO_INCREMENT", "CURRENT_DATE", "CURRENT_USER", "DEFAULT", "CURRENT_TIMESTAMP()", "CURTIME()", "CURDATE()", "DAYNAME()", "DAYOFMONTH()", "DAYOFWEEK()", "DAYOFYEAR()");

    public function __construct($table = null, $debug = false)
    {
        $this->table = $table;
        $this->debug = $debug;

        $this->selection = array("*");
    }

    public function setLogging($bool)
    {
        $this->logging = $bool;
    }

    public function where($arg1, $operator = 'AND', $arg2 = null)
    {
        if (is_array($arg1) && $arg2 == null) {
            foreach ($arg1 as $arg) {
                if (!is_array($arg) || count($arg) != 3) {
                    throw new \InvalidArgumentException(
                        'Invalid nested where arguments!
                        Expected argument to be array of length 3, got '. gettype($arg) .' of length '.count($arg).'!'
                    );
                }

                $this->where($arg[0], $arg[1], $arg[2]);
            }
            return;
        }

        if (is_null($arg2)) {
            $arg2 = $operator;
            $operator = '=';
        }
        try {
            $this->where[] = new QueryWhere($arg1, $operator, $arg2, self::getTableName($this->table));
        } catch (Exception $e) {
            //echo "Fehler: {$e->getMessage()}";
            return false;
        }

        return $this;
    }

    public function next($id, $selectolumns = null, $column = 'id')
    {
        if ($selectolumns != null) {
            $this->select($selectolumns);
        }
        $this->where($column, ">", $id)->orderBy($column, "ASC")->limit(1);

        return $this;
    }

    public function count()
    {
        $this->selection = array(self::alias('COUNT(*)', 'count'));
        $result = $this->get();

        return $result[0]["count"];
    }

    public function prev($id, $selectolumns = null, $column = 'id')
    {
        if ($selectolumns != null) {
            $this->select($selectolumns);
        }
        $this->where($column, "<", $id)->orderBy($column, "DESC")->limit(1);

        return $this;
    }

    public function orderBy($column, $type = 'DESC')
    {
        if (!in_array($column, array("RAND()")) && !$column instanceof Raw) {
            $col = $this->sanitizeColumnName($column);
        } else {
            $col =  $column;
        }

        $this->order[] = $col . " " . strtoupper($type);

        return $this;
    }

    public function groupBy($arg)
    {
        if (is_array($arg)) {
            foreach ($arg as $a) {
                $this->group[] = $this->sanitizeColumnName($a);
            }
        } else {
            $this->group[] = $this->sanitizeColumnName($arg);
        }

        return $this;
    }

    public function limit($count)
    {
        $this->limit[] = $count;

        return $this;
    }

    public function select($columns)
    {
        if ($this->selection[0] == "*") {
            $this->selection = array();
        }

        if (is_array($columns)) {
            /*foreach($columns as &$column) {
                $column = $this->sanitizeColumnName($column);
            }*/

            $this->selection = array_merge($this->selection, $columns);
        } else {
            if (!in_array($columns, $this->selection)) {
                $this->selection[] = $columns;
            }
        }

        return $this;
    }

    public function join($table, $column, $operator = null, $joinedColumn = null, $type = 'LEFT')
    {
         $joinObject = new QueryJoin($table, $this, $type);
        if ($operator != null) {
            $joinObject->on($column, $operator, $joinedColumn);
        }

         $this->joins[] = $joinObject;
         return $joinObject;
    }

    public function getSelectStatement()
    {
        $tmp = $this->selection;

        foreach ($tmp as $idx => $select) {
            if ($select instanceof \App\QueryBuilder\QueryAlias) {
                $column = $select->get('name');
            } else {
                $column = $select;
            }

            if (!$column instanceof Raw && !in_array($column, self::$sqlKeywords)) {
                $column = $this->sanitizeColumnName($column);
            }

            if ($select instanceof \App\QueryBuilder\QueryAlias) {
                $column .= ' as '. $select->get("alias");
            }

            $tmp[$idx] = $column;
        }

        /*array_walk($tmp, function(&$select) use(&self::$sqlKeywords) {
            if($select instanceof \App\QueryBuilder\QueryAlias) $column = $select->get('name');
            else $column = $select;

            if(!in_array($column, self::$sqlKeywords)) {
                $column = $this->sanitizeColumnName($column);
            }

            vd($select);

            if($select instanceof \App\QueryBuilder\QueryAlias) $column .= ' as '. $select->get("alias");

            $select = $column;
        }); */

        return join(", ", $tmp);
    }

    public function getFullSQL()
    {
        $sql = $this->getSQL();
        $bindings = $this->getCriteriaBindings();

        foreach ($bindings as $b) {
            if (is_string($b)) {
                $b = "'{$b}'";
            }
            $sql = preg_replace('/\?/', $b, $sql, 1);
        }

        return $sql;
    }

    public function get()
    {
        $sql = $this->getSQL();
        $args = $this->getCriteriaBindings();

        if ($this->debug) {
            vd($sql);
            vd($args);
        }

        return $this->query($sql, $args);
    }

    public function result()
    {
        return isset($this->result) ? $this->result : null;
    }

    public function setTable($table)
    {
        $this->table = $table;
        $this->reset();

        return $this;
    }

    public function reset($prop = null)
    {
        if (is_null($prop)) {
            $this->joins = array();
            $this->where = array();
            $this->selection = array("*");
            $this->order = array();
            $this->group = array();
            $this->limit = array();
        } else {
            if (in_array($prop, array('limit', 'selection', 'where', 'order', 'joins', 'group'))) {
                $this->$prop = array();
            }
        }

        return $this;
    }

    protected function query($sql, $bindings = array())
    {
        try {
            $query = new Query($sql, $this->logging);
            $query->execute($bindings);
            $this->result = $query->getResult();
            $this->lastInsertID = $query->lastInsertId();

            //$this->reset();

            return $this->result;
        } catch (\Exception $e) {
            $this->result = null;
            $this->last_exception = $e;
        }

        return false;
    }

    public function describe()
    {
        return $this->query("SHOW FULL COLUMNS FROM ". self::getTableName($this->table));
    }

    public function lastInsertId()
    {
        return $this->lastInsertID;
    }

    public function getError()
    {
        return !isset($this->last_exception) ? null : self::getErrorMessage($this->last_exception);
    }

    public function update(array $data)
    {
        list($statement, $bindings) = $this->getUpdateStatement($data);
        $criteria = $this->getCriteriaString();
        $whereBindings = $this->getCriteriaBindings();

        $bindings = array_merge($bindings, $whereBindings);
        $limit = !empty($this->limit) ? 'LIMIT ' . join(", ", $this->limit) : '';

        $sql = array(
            "UPDATE",
            $this->sanitize(self::getTableName($this->table)),
            "SET " . $statement,
            $criteria,
            $limit
        );

        $sql = join(" ", $sql);

        if ($this->debug) {
            vd($sql);
            vd($bindings);
        }
        return $this->query($sql, $bindings);
    }

    public function delete()
    {
        return $this->update(
            array(
            "deleted" => "1"
            )
        );
    }

    public function insertIgnore(array $data)
    {
        list($statement, $bindings) = $this->getInsertStatement($data);

        $sql = array(
            "INSERT IGNORE INTO",
            $this->sanitize(self::getTableName($this->table)),
            $statement
        );

        $sql = join(" ", $sql);

        //vd($sql);

        return $this->query($sql, $bindings) !== false;
    }


    public function insert(array $data)
    {
        list($statement, $bindings) = $this->getInsertStatement($data);

        $sql = array(
            "INSERT INTO",
            $this->sanitize(self::getTableName($this->table)),
            $statement
        );

        $sql = join(" ", $sql);

        if ($this->debug) {
            vd($sql);
            vd($bindings);
        }

        return $this->query($sql, $bindings) !== false;
    }

    private function getCriteriaString()
    {
        if (count($this->where) > 0) {
            $clauses = array();
            foreach ($this->where as $clause) {
                $clauses[] = $clause->getClause(self::getTableName($this->table));
            }
            return ' WHERE ' . implode($clauses, ' AND ');
        }

        return '';
    }

    private function getCriteriaBindings()
    {
        $bindings = array();
        
        if (count($this->where) > 0) {
            foreach ($this->where as $clause) {
                $bindings = array_merge($bindings, $clause->getBindings());
            }
        }

        return $bindings;
    }

    public function getSQL()
    {
        $selection = $this->getSelectStatement();

        $join_statement = array();

        if (!empty($this->joins)) {
            $join_selection = array();

            foreach ($this->joins as $join) {
                $join_selection[] = $join->getSelectStatement();
                $join_statement[] = $join->getStatement();
            }

            $selection .= ', ' . implode($join_selection, ', ');
        }

        $join_statement = implode($join_statement, ' ');

        $sql = "SELECT $selection FROM " . $this->sanitize(self::getTableName($this->table));
        $sql .= $join_statement;

        if (count($this->where) > 0) {
            $sql .= $this->getCriteriaString();
        }

        if (count($this->group) > 0) {
            $sql .= " GROUP BY ". join($this->group, ", ");
        }

        if (count($this->order) > 0) {
            $sql .= " ORDER BY ". join($this->order, ", ");
        }

        if (count($this->limit) > 0) {
            $sql .= " LIMIT ". join($this->limit, ", ");
        }

        return $sql;
    }

    public function __toString()
    {
        return $this->getSQL();
    }

    protected function getInsertStatement($data)
    {
        $insert = array();
        $values = array();
        $bindings = array();

        foreach ($data as $key => $value) {
            if (in_array($value, self::$sqlKeywords)) {
                $values[] = $value;
            } elseif (is_null($value) || strlen($value) == 0) {
                $values[] = "NULL";
            } else {
                $bindings[] = $value;
                $values[] = "?";
            }

            $insert[] = $this->sanitize($key);
        }

        $statement = "(" . join(", ", $insert) . ") VALUES (" . join(", ", $values) . ")";

        return array($statement, $bindings);
    }

    private function getUpdateStatement($data)
    {
        $values = array();
        $statement = "";

        foreach ($data as $key => $value) {
            if (is_null($value) || (empty($value) && $value !== 0)) {
                $statement.= $this->sanitizeColumnName($key) . " = NULL, ";
            } elseif (in_array($value, self::$sqlKeywords)) {
                $statement.= $this->sanitizeColumnName($key) . " = $value, ";
            } else {
                $statement .= $this->sanitizeColumnName($key) . " = ?, ";
                $values[] = $value;
            }
        }

        $statement = rtrim($statement, ', ');

        return array($statement, $values);
    }

    /*private function getQuery($type = "select", $data = array()) {
        $allowedTypes = array('select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly');
        if (!in_array(strtolower($type), $allowedTypes)) {
            throw new Exception("$type ist kein gültiger Typ!");
        }

        return "hallo";
    }*/

    final public function sanitizeColumnName($column)
    {
        $sanitizedColumn = $column == "*" ? $column : $this->sanitize($column);
        return $this->sanitize(self::getTableName($this->table)) . "." . $sanitizedColumn;
    }

    final public function sanitize($value)
    {
        return self::SANITIZER . $value . self::SANITIZER;
    }

    private static function getErrorMessage($e)
    {
        // TODO
        $errorMessage = "Es ist ein Fehler bei der Verarbeitung aufgetreten!";
        $errorValue = null;
        $errorColumn = null;
        $errorCode = -1;

        if (preg_match("/: ([0-9]+) /", $e->getMessage(), $matches)) {
            $errorCode = $matches[1];
            switch ($errorCode) {
                case 1062: // Duplicate Index
                    if (preg_match("/entry '(.[^']+)'/", $e->getMessage(), $m)) {
                        $errorMessage = "Es gibt bereits einen Eintrag mit dem Wert <i>{$m[1]}</i>!";
                        $errorValue = $m[1];
                    }
                    break;

                case 1048: // Column must not be null
                    if (preg_match("/Column '(.[^']+)'/", $e->getMessage(), $m)) {
                        $errorMessage = "Dieser Wert darf nicht leer sein!";
                        $errorColumn = $m[1];
                    }
                    break;
            }
        }

        return array($errorCode, $errorMessage, $errorValue, $errorColumn);
    }

    public static function alias($field, $alias)
    {
        return new QueryAlias($field, $alias);
    }

    /*public static function raw($sql) {
        $query = new QueryBuilderRaw($sql);
        return $query;
    }*/

    public static function raw($column)
    {
        return new Raw($column);
    }

    public static function getTableName($table)
    {
        if (is_null(self::$tablePrefix)) {
            return $table;
        }
        return self::$tablePrefix . "_{$table}";
    }

    public static function setTablePrefix($prefix)
    {
        self::$tablePrefix = $prefix;
    }
}
