<?php
namespace App\QueryBuilder;

class QueryJoin
{
    use \App\Traits\GetSet;

    protected $table;
    protected $tableOnJoin;
    protected $type;

    protected $selection = array("*");
    protected $on;

    public function __construct($table, $tableOnJoin, $type = "inner")
    {
        $this->table = $table;
        $this->type = $type;
        $this->table_on_join = $tableOnJoin;

        return $this;
    }

    public function on($joinedColumn, $operator, $ownColumn)
    {
        $this->on[] = array(
            $this->table_on_join->sanitizeColumnName($joinedColumn),
            $operator,
            $this->sanitizeColumnName($ownColumn)
        );

        return $this;
    }

    public function select($columns)
    {
        if ($this->selection[0] == "*") {
            $this->selection = array();
        }

        if (is_array($columns)) {
            $this->selection = array_merge($this->selection, $columns);
        } else {
            if (!in_array($columns, $this->selection)) {
                $this->selection[] = $columns;
            }
        }

        return $this;
    }

    public function getStatement()
    {
        $tableName = Builder::getTableName($this->table);

        $statement = " ". strtoupper($this->type) . " JOIN `{$tableName}` ON ";
        foreach ($this->on as $on) {
            $statement .= join(' ', $on);
        }

        return $statement;
    }

    public function getSelectStatement()
    {
        $tmp = $this->selection;
        array_walk(
            $tmp,
            function (&$select) {
                if ($select instanceof QueryAlias) {
                    $select = $this->sanitizeColumnName($select->get("name")) . " as ". $select->get("alias");
                } else {
                    $select = $this->sanitizeColumnName($select);
                }
            }
        );

        return join(", ", $tmp);
    }

    final public function sanitizeColumnName($column)
    {
        $sanitizedColumn = $column == "*" ? $column : $this->sanitize($column);
        return $this->sanitize(Builder::getTableName($this->table)) . "." . $sanitizedColumn;
    }

    final public function sanitize($value)
    {
        return Builder::SANITIZER . $value . Builder::SANITIZER;
    }
}
