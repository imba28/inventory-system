<?php
namespace App\QueryBuilder;

class Builder {
    protected $table;

    const SANITIZER = "`";

    protected $debug;
    protected $logging = true;

    protected $joins = array();
    protected $where = array();
    protected $selection = array();
    protected $order = array();
    protected $limit;

    protected $result;
    protected $lastInsertID;
    protected $last_exception;

    protected static $sql_keywords = array("NOW()", "COUNT(*)", "AUTO_INCREMENT", "CURRENT_DATE", "CURRENT_USER", "DEFAULT", "CURRENT_TIMESTAMP()", "CURTIME()", "CURDATE()", "DAYNAME()", "DAYOFMONTH()", "DAYOFWEEK()", "DAYOFYEAR()");

    public function __construct($table, $debug = false){
        $this->table = $table;
        $this->debug = $debug;

        $this->selection = array("*");
    }

    public function setLogging($bool) {
        $this->logging = $bool;
    }

    public function where($arg1, $operator, $arg2){
        try {
            $this->where[] = new QueryWhere(array($arg1, $operator, $arg2));
        }
        catch(Exception $e){
            //echo "Fehler: {$e->getMessage()}";
            return false;
        }
        return $this;
    }

    public function whereVisible(){
        $this->where("visible", "=", "1")->where("deleted", "=", "0");
        return $this;
    }


    public function next($id = null, $select_columns = array("ID"), $column = null){
        $column = is_null($column) ? "ID" : $column;

        $this->select($select_columns)->where($column, ">", $id)->orderBy($column, "ASC")->limit(1);

        return $this;
    }

    public function count(){
        $this->selection = array(self::alias('COUNT(*)', 'count'));
        $result = $this->get();

        return $result[0]["count"];
    }

    public function prev($id, $select_columns = array("ID"), $column = null){
        $column = is_null($column) ? "ID" : $column;

        $this->select($select_columns)->where($column, "<", $id)->orderBy($column, "DESC")->limit(1);

        return $this;
    }


    public function orderBy($column, $type){
        $col = !in_array($column, array("RAND()")) ? $this->sanitizeColumnName($column) : $column;
        $this->order[] = $col . " " . strtoupper($type);

        return $this;
    }

    public function limit($count){
        $this->limit[] = $count;

        return $this;
    }

    public function select($columns){
        if($this->selection[0] == "*") $this->selection = array();

        if(is_array($columns)){
            /*foreach($columns as &$column){
                $column = $this->sanitizeColumnName($column);
            }*/

            $this->selection = array_merge($this->selection, $columns);
        }
        else{
            if(!in_array($columns, $this->selection)) $this->selection[] = $columns;
        }

        return $this;
    }

    public function join($table, $key, $operator = null, $value = null, $type = 'LEFT'){
         $joinObject = new QueryJoin($table, $this, $type);
         $joinObject->on($key, $operator, $value);

         $this->joins[] = $joinObject;
         return $joinObject;
    }

    public function getSelectStatement() {
        $tmp = $this->selection;
        $sql_keywords = self::$sql_keywords;

        foreach($tmp as $idx => $select) {
            if($select instanceof \App\QueryBuilder\QueryAlias) {
                $column = $select->get('name');
            }
            else {
                $column = $select;
            }

            if(!in_array($column, $sql_keywords)) {
                $column = $this->sanitizeColumnName($column);
            }

            if($select instanceof \App\QueryBuilder\QueryAlias) $column .= ' as '. $select->get("alias");

            $tmp[$idx] = $column;
        }

        /*array_walk($tmp, function(&$select) use(&$sql_keywords) {
            if($select instanceof \App\QueryBuilder\QueryAlias) $column = $select->get('name');
            else $column = $select;

            if(!in_array($column, $sql_keywords)) {
                $column = $this->sanitizeColumnName($column);
            }

            vd($select);

            if($select instanceof \App\QueryBuilder\QueryAlias) $column .= ' as '. $select->get("alias");

            $select = $column;
        }); */

        return join(", ", $tmp);
    }

    public function get(){
        $table_name = getTableName($this->table);

        $selection = $this->getSelectStatement();

        $join_selection = "";
        $join_statement = "";

        if(!empty($this->joins)){
            $join_selection = ", ";
            foreach($this->joins as $join){
                $join_selection .= $join->getSelectStatement().", ";
                $join_statement .= $join->getStatement() . " ";
            }
            $join_selection = rtrim($join_selection , ", ");
        }

        $sql = "SELECT $selection $join_selection FROM " . $this->sanitize($table_name);
        $sql .= $join_statement;

        $args = array();

        if(count($this->where) > 0){
            /*
            $sql .= " WHERE ";
            foreach($this->where as $clause){
                $sql.= $clause->getCondition($table_name) . " AND";
                $val = $clause->getValue();
                if($val !== false){
                   // vd($val);
                    if(is_array($val)){
                        foreach($val as $v) if(!is_null($v)) $args[] = $v;
                    }
                    else $args[] = $val;
                }
            }
            $sql = rtrim($sql, " AND");*/
            list($criteria, $args) = $this->getCriteria();
            $sql .= $criteria;
        }

        if(count($this->order) > 0){
            $sql .= " ORDER BY ". join($this->order, ", ");
        }

        if(count($this->limit) > 0){
            $sql .= " LIMIT ". join($this->limit, ", ");
        }

        if($this->debug){
            vd($sql);
            vd($args);
        }

        return $this->query($sql, $args);
    }

    public function result(){
        return isset($this->result) ? $this->result : null;
    }

    public function setTable($table){
        $this->table = $table;
        $this->reset();

        return $this;
    }

    public function reset($prop = null){
        if(is_null($prop)) {
            $this->selection = array("*");
            $this->where = array();
            $this->order = array();
            $this->joins = array();
        }
        else {
            if(in_array($prop, array('limit', 'selection', 'where', 'order', 'joins'))) {
                $this->$prop = array();
            }
        }

        return $this;
    }

    protected function query($sql, $bindings = array()){
        try {
            $query = new Query($sql, $this->logging);
            $query->execute($bindings);
            $this->result = $query->getResult();
            $this->lastInsertID = $query->lastInsertId();

            //$this->reset();

            return $this->result;
        }
        catch(\Exception $e){
            $this->result = null;
            $this->last_exception = $e;
        }

        return false;
    }

    public function describe(){
        return $this->query("SHOW FULL COLUMNS FROM ". getTableName($this->table));
    }
    public function lastInsertId(){
        return $this->lastInsertID;
    }
    public function getError(){
        return !isset($this->last_exception) ? null : self::getErrorMessage($this->last_exception);
    }

    public function find($id, $column = null){
        $column = is_null($column) ? "ID" : $column;

        $this->where[] = new QueryWhere(array($column, "=", $id));

        return $this->get();
    }

    public function update(array $data){
        list($statement, $bindings) = $this->getUpdateStatement($data);
        list($criteria, $where_bindings) = $this->getCriteria();

        $bindings = array_merge($bindings, $where_bindings);
        $limit = isset($this->limit) ? 'LIMIT ' . join(", ", $this->limit) : '';

        $sql_arr = array(
            "UPDATE",
            $this->sanitize(getTableName($this->table)),
            "SET " . $statement,
            $criteria,
            $limit
        );

        $sql = join(" ", $sql_arr);

        if($this->debug){
            vd($sql);
            vd($bindings);
        }
        return $this->query($sql, $bindings);
    }

    public function delete(){
        return $this->update(array(
            "deleted" => "1"
        ));
    }

    public function insertIgnore(array $data){
        list($statement, $bindings) = $this->getInsertStatement($data);

        $sql_arr = array(
            "INSERT IGNORE INTO",
            $this->sanitize(getTableName($this->table)),
            $statement
        );

        $sql = join(" ", $sql_arr);

        //vd($sql);

        return $this->query($sql, $bindings) !== false;
    }


    public function insert(array $data){
        list($statement, $bindings) = $this->getInsertStatement($data);

        $sql_arr = array(
            "INSERT INTO",
            $this->sanitize(getTableName($this->table)),
            $statement
        );

        $sql = join(" ", $sql_arr);

        if($this->debug){
            vd($sql);
            vd($bindings);
        }

        return $this->query($sql, $bindings) !== false;
    }

    private function getCriteria(){
        $bindings = array();
        $criteria = "";
        $table_name = getTableName($this->table);

        if(count($this->where) > 0){
            $criteria = " WHERE ";
            foreach($this->where as $clause){
                $criteria .= $clause->getCondition($table_name) . " AND";
                $val = $clause->getValue();
                if($val !== false){
                    if(is_array($val)) foreach($val as $v) if(!is_null($v)) $bindings[] = $v;
                    else $bindings[] = $val;
                }
            }
            $criteria = rtrim($criteria, " AND");
        }

        return array($criteria, $bindings);
    }

    private function getInsertStatement($data){
        $insert = array();
        $values = array();
        $bindings = array();

        foreach($data as $key => $value){
            if(in_array($value, self::$sql_keywords)) $values[] = $value;
            else {
                $bindings[] = $value;
                $values[] = "?";
            }

            $insert[] = $this->sanitize($key);
        }

        $statement = "(" . join(", ", $insert) . ") VALUES (" . join(", ", $values) . ")";

        return array($statement, $bindings);
    }

    private function getUpdateStatement($data){
        $values = array();
        $statement = "";

        foreach($data as $key => $value){
            if(is_null($value) || (empty($value) && $value !== 0)) {
                $statement.= $this->sanitizeColumnName($key) . " = NULL, ";
            }
            elseif(in_array($value, self::$sql_keywords)) $statement.= $this->sanitizeColumnName($key) . " = $value, ";
            else {
                $statement .= $this->sanitizeColumnName($key) . " = ?, ";
                $values[] = $value;
            }
        }

        $statement = rtrim($statement, ', ');

        return array($statement, $values);
    }

    /*private function getQuery($type = "select", $data = array()){
        $allowedTypes = array('select', 'insert', 'insertignore', 'replace', 'delete', 'update', 'criteriaonly');
        if (!in_array(strtolower($type), $allowedTypes)) {
            throw new Exception("$type ist kein gültiger Typ!");
        }

        return "hallo";
    }*/

    public final function sanitizeColumnName($column){
        $sanitized_column = $column == "*" ? $column : $this->sanitize($column);
        return $this->sanitize(getTableName($this->table)) . "." . $sanitized_column;
    }

    public final function sanitize($value){
        return self::SANITIZER . $value . self::SANITIZER;
    }

    private static function getErrorMessage($e){ // TODO
        $error_message = "Es ist ein Fehler bei der Verarbeitung aufgetreten!";
        $error_value = null;
        $error_column = null;
        $error_code = -1;

        if(preg_match("/: ([0-9]+) /", $e->getMessage(), $matches)){
            $error_code = $matches[1];
            switch($error_code){
                case 1062: // Duplicate Index
                    if(preg_match("/entry '(.[^']+)'/", $e->getMessage(), $m)){
                        $error_message = "Es gibt bereits einen Eintrag mit dem Wert <i>{$m[1]}</i>!";
                        $error_value = $m[1];
                    }
                    break;

                case 1048: // Column must not be null
                    if(preg_match("/Column '(.[^']+)'/", $e->getMessage(), $m)){
                        $error_message = "Dieser Wert darf nicht leer sein!";
                        $error_column = $m[1];
                    }
                    break;
            }
        }

        return array($error_code, $error_message, $error_value, $error_column);
    }

    public static function alias($field, $alias){
        return new QueryAlias($field, $alias);
    }

    public static function raw($sql){
        $query = new QueryBuilderRaw($sql);
        return $query;
    }
}
?>