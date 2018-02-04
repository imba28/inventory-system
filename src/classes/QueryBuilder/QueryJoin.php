<?php
namespace App\QueryBuilder;

class QueryJoin {
    use \App\Traits\GetSet;

    protected $table;
    protected $table_on_join;
    protected $type;

    protected $selection = array("*");
    protected $on;

    public function __construct($table, $table_on_join, $type = "inner") {
        $this->table = $table;
        $this->type = $type;
        $this->table_on_join = $table_on_join;

        return $this;
    }

    public function on($joinedColumn, $operator, $ownColumn) {
        $this->on[] = array($this->table_on_join->sanitizeColumnName($joinedColumn), $operator, $this->sanitizeColumnName($ownColumn));

        return $this;
    }

    public function select($columns) {
        if($this->selection[0] == "*") $this->selection = array();

        if(is_array($columns)) {
            $this->selection = array_merge($this->selection, $columns);
        }
        else{
            if(!in_array($columns, $this->selection)) $this->selection[] = $columns;
        }

        return $this;
    }

    public function getStatement() {
        $table_name = Builder::getTableName($this->table);

        $statement = " ". strtoupper($this->type) . " JOIN `{$table_name}` ON ";
        foreach($this->on as $on) {
            $statement .= join(' ', $on);
        }

        return $statement;
    }

    public function getSelectStatement() {
        $tmp = $this->selection;
        array_walk($tmp, function(&$select) {
            if($select instanceof QueryAlias) {
                $select = $this->sanitizeColumnName($select->get("name")) . " as ". $select->get("alias");
            }
            else $select = $this->sanitizeColumnName($select);
        });

        return join(", ", $tmp);
    }

    public final function sanitizeColumnName($column) {
        $sanitized_column = $column == "*" ? $column : $this->sanitize($column);
        return $this->sanitize(Builder::getTableName($this->table)) . "." . $sanitized_column;
    }

    public final function sanitize($value) {
        return Builder::SANITIZER . $value . Builder::SANITIZER;
    }
}
?>