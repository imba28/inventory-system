<?php
namespace App\QueryBuilder;

class QueryJoin {
    use App\Traits\get_set;

    protected $table;
    protected $table_on_join;
    protected $type;

    protected $selection = array("*");
    protected $on;

    public function __construct($table, $table_on_join, $type = "inner"){
        $this->table = $table;
        $this->type = $type;
        $this->table_on_join = $table_on_join;

        return $this;
    }

    public function on($key, $operator, $value){
        $this->on[] = array($this->table_on_join->sanitizeColumnName($key), $operator, $this->sanitizeColumnName($value));

        return $this;
    }

    public function select($columns){
        if($this->selection[0] == "*") $this->selection = array();

        if(is_array($columns)){
            $this->selection = array_merge($this->selection, $columns);
        }
        else{
            if(!in_array($columns, $this->selection)) $this->selection[] = $columns;
        }

        return $this;
    }

    public function getStatement(){
        $table_name = db_table_name($this->table);

        $statement = " ". strtoupper($this->type) . " JOIN $table_name ON ";
        foreach($this->on as $on){
            $statement .= join(' ', $on);
        }

        return $statement;
    }

    public function getSelectStatement(){
        $tmp = $this->selection;
        array_walk($tmp, function(&$select){
            if($select instanceof QueryAlias){
                $select = $this->sanitizeColumnName($select->get("name")) . " as ". $select->get("alias");
            }
            else $select = $this->sanitizeColumnName($select);
        });

        return join(", ", $tmp);
    }

    public final function sanitizeColumnName($column){
        $sanitized_column = $column == "*" ? $column : $this->sanitize($column);
        return $this->sanitize(db_table_name($this->table)) . "." . $sanitized_column;
    }

    public final function sanitize($value){
        return QueryBuilder::SANITIZER . $value . QueryBuilder::SANITIZER;
    }
}
?>
