<?php
namespace App\QueryBuilder;

class QueryWhere {
    protected $argument_1;
    protected $argument_2;
    protected $operator;

    private static $valid_operators = array("=", "<>", "!=", "<", ">", ">=", "<=", "!<", "!>", "LIKE", "IS", "IS NOT", "OR", "AND");

    public function __construct(array $where){
        self::validateClause($where);

        if(is_array($where[0])) $where[0] = new QueryWhere($where[0]);
        if(is_array($where[2])) $where[2] = new QueryWhere($where[2]);

        $this->argument_1 = $where[0];
        $this->argument_2 = $where[2];
        $this->operator = $where[1];
    }

    protected static function validateClause($where){
        if(count($where) != 3) throw new \InvalidArgumentException("Bedingungen für Where-Clause sind ungültig!");

        if((is_array($where[0]) && count($where[0]) < 0) || ($where[0] != "0" && empty($where[0]))) throw new \InvalidArgumentException("Argument 1 `$where[0]` für Where-Clause ist ungültig!");
        if((is_array($where[2]) && count($where[2]) < 0) /*|| ($where[2] != "0" && empty($where[2]))*/) throw new \InvalidArgumentException("Argument 2 `$where[2]` für Where-Clause ist ungültig!");
        if(!in_array(strtoupper($where[1]), QueryWhere::$valid_operators)) throw new \InvalidArgumentException("Operator `$where[1]` für Where-Clause ist ungültig!");
    }

    public function getCondition($table){
        $arg1 = $this->argument_1 instanceof QueryWhere ? $this->argument_1->getCondition($table) : QueryWhere::sanitize($this->argument_1, $table);
        $arg2 = $this->argument_2 instanceof QueryWhere ? $this->argument_2->getCondition($table) : $this->argument_2;

        if(!$this->argument_2 instanceof QueryWhere){
            if(is_null($arg2) || $arg2 === "NULL") $arg2 = "NULL";
            else $arg2 = "?";
        }

        return "(" . join(" ", array($arg1, $this->operator, $arg2)) . ")";
    }

    public function getValue(){
        if($this->argument_1 instanceof QueryWhere || $this->argument_2 instanceof QueryWhere){
            $ret = array();
            if($this->argument_1 instanceof QueryWhere){
                foreach($this->argument_1->getValue() as $v) if($v !== false) $ret[] = $v;
            }
            elseif(!is_null($this->argument_1) && $this->argument_1 !== "NULL") $ret[] = $this->argument_1;

            if($this->argument_2 instanceof QueryWhere){
                foreach($this->argument_2->getValue() as $v) if($v !== false) $ret[] = $v;
            }
            elseif(!is_null($this->argument_2) && $this->argument_2 !== "NULL") $ret[] = $this->argument_2;

            return $ret;
        }
        elseif(!is_null($this->argument_2) && $this->argument_2 !== "NULL") return array($this->argument_2);
    }

    private function getColumnName($table, $column){
        return $this->sanitizer;
    }

    private final static function sanitize($value, $table){
        return Builder::SANITIZER . $table . Builder::SANITIZER . "." . Builder::SANITIZER . $value . Builder::SANITIZER;
    }
}
?>